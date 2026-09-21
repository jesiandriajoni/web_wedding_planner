<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SeserahanItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeserahanController extends Controller
{
    public function index(Project $project)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $items = $project->seserahanItems()->get();

        return Inertia::render('Projects/Seserahan', [
            'project' => $project,
            'items' => $items,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:pending,purchased,delivered,returned',
            'tracking_url' => 'nullable|url|max:2000', // validates http/https url format
        ]);

        $project->seserahanItems()->create([
            'item_name' => $request->name,
            'status' => $request->status,
            'tracking_url' => $request->tracking_url,
        ]);

        return back();
    }

    public function update(Request $request, Project $project, SeserahanItem $seserahan)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:pending,purchased,delivered,returned',
            'tracking_url' => 'nullable|url|max:2000',
        ]);

        $seserahan->update([
            'item_name' => $request->name,
            'status' => $request->status,
            'tracking_url' => $request->tracking_url,
        ]);

        return back();
    }

    public function destroy(Project $project, SeserahanItem $seserahan)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $seserahan->delete();

        return back();
    }
}
