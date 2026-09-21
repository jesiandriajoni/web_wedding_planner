<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;

    public $total_checklists;

    public $done_checklists;

    public $progress;

    public $spent_budget;

    public $upcoming_tasks;

    public $total_budget;

    public function __construct(Project $project)
    {
        $this->projectId = $project->id;
        $this->total_budget = (float) $project->total_budget;

        // Aggregate checklists
        $this->total_checklists = $project->checklists()->count();
        $this->done_checklists = $project->checklists()->where('status', 'done')->count();

        // Calculate progress
        $this->progress = $this->total_checklists > 0
            ? (int) round(($this->done_checklists / $this->total_checklists) * 100)
            : 0;

        // Aggregate budget (actual spending)
        $this->spent_budget = (float) $project->vendors()->sum('paid_amount');

        // Load upcoming tasks
        $this->upcoming_tasks = $project->checklists()
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('projects.'.$this->projectId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dashboard.updated';
    }
}
