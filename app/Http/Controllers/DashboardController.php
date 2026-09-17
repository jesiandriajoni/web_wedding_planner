<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(Project $project)
    {
        // Enforce member check
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Aggregate checklists
        $totalChecklists = $project->checklists()->count();
        $doneChecklists = $project->checklists()->where('status', 'done')->count();

        // Calculate progress with division by zero guard
        $progress = $totalChecklists > 0 ? (int) round(($doneChecklists / $totalChecklists) * 100) : 0;

        // Aggregate budget (actual spending)
        $spentBudget = (float) $project->vendors()->sum('paid_amount');

        // Load upcoming tasks
        $upcomingTasks = $project->checklists()
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->limit(5)
            ->get();

        return Inertia::render('Projects/Dashboard', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'total_budget' => (float) $project->total_budget,
            ],
            'total_checklists' => $totalChecklists,
            'done_checklists' => $doneChecklists,
            'progress' => $progress,
            'spent_budget' => $spentBudget,
            'upcoming_tasks' => $upcomingTasks,
        ]);
    }
}
