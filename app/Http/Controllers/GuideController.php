<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Inertia\Inertia;

class GuideController extends Controller
{
    public function show(Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Projects/Guide', [
            'project' => $project
        ]);
    }
}
