<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChecklistController extends Controller
{
    private function authorizeMember(Project $project): void
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function ensureOwned(Project $project, Checklist $checklist): void
    {
        if ($checklist->project_id !== $project->id) {
            abort(404);
        }
    }

    public function index(Project $project)
    {
        $this->authorizeMember($project);

        return Inertia::render('Projects/Checklist', [
            'project' => $project,
            'checklists' => $project->checklists()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorizeMember($project);

        $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'nullable|in:wo,pengantin,keluarga',
        ]);

        $project->checklists()->create([
            'title' => $request->title,
            'assigned_to' => $request->assigned_to,
            'status' => 'pending',
        ]);

        return back();
    }

    public function update(Request $request, Project $project, Checklist $checklist)
    {
        $this->authorizeMember($project);
        $this->ensureOwned($project, $checklist);

        $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'nullable|in:wo,pengantin,keluarga',
            'status' => 'required|in:pending,done',
        ]);

        $checklist->update($request->only('title', 'assigned_to', 'status'));

        return back();
    }

    public function destroy(Project $project, Checklist $checklist)
    {
        $this->authorizeMember($project);
        $this->ensureOwned($project, $checklist);

        $checklist->delete();

        return back();
    }
}
