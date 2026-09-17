<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rundown;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class RundownController extends Controller
{
    public function index(Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $rundowns = $project->rundowns()->orderBy('time')->get();

        return Inertia::render('Projects/Rundowns', [
            'project' => $project,
            'rundowns' => $rundowns
        ]);
    }

    public function store(Request $request, Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'time' => 'required|regex:/^\d{2}:\d{2}$/', // validates HH:MM format
            'activity' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|string|max:255',
        ]);

        $project->rundowns()->create([
            'time' => $request->time,
            'activity' => $request->activity,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
        ]);

        return back();
    }

    public function update(Request $request, Project $project, Rundown $rundown)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'time' => 'required|regex:/^\d{2}:\d{2}$/',
            'activity' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|string|max:255',
        ]);

        $rundown->update([
            'time' => $request->time,
            'activity' => $request->activity,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
        ]);

        return back();
    }

    public function destroy(Project $project, Rundown $rundown)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $rundown->delete();

        return back();
    }

    public function exportPdf(Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure temporary folder exists for dompdf write stability
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        $rundowns = $project->rundowns()->orderBy('time')->get();

        // Simple styled HTML for PDF render
        $html = view('pdf.rundown', [
            'project' => $project,
            'rundowns' => $rundowns
        ])->render();

        $pdf = Pdf::loadHTML($html);
        
        return $pdf->download("Rundown-Pernikahan-{$project->slug}.pdf");
    }
}
