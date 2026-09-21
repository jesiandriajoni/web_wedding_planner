<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_route_requires_auth_and_member(): void
    {
        $project = Project::create([
            'name' => 'Test Wedding',
            'slug' => 'test-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);

        $response = $this->get("/projects/{$project->id}/dashboard");
        $response->assertRedirect('/login');
    }

    public function test_dashboard_handles_division_by_zero_when_checklists_empty(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Test Wedding',
            'slug' => 'test-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);
        $response = $this->get("/projects/{$project->id}/dashboard");
        $response->assertStatus(200);

        // Verify Inertia data contains progress of 0
        $response->assertInertia(fn ($page) => $page
            ->component('Projects/Dashboard')
            ->where('progress', 0)
            ->where('total_checklists', 0)
            ->where('done_checklists', 0)
        );
    }

    public function test_dashboard_calculates_progress_correctly(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Test Wedding',
            'slug' => 'test-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        // Create 2 checklists, 1 done, 1 pending
        Checklist::create(['project_id' => $project->id, 'title' => 'Task A', 'status' => 'done']);
        Checklist::create(['project_id' => $project->id, 'title' => 'Task B', 'status' => 'pending']);

        // Create a vendor with DP
        Vendor::create([
            'project_id' => $project->id,
            'name' => 'Catering X',
            'category' => 'Catering',
            'contact' => '081',
            'package_price' => 10000000.00,
            'paid_amount' => 4000000.00,
            'status' => 'dp',
        ]);

        $this->actingAs($user);
        $response = $this->get("/projects/{$project->id}/dashboard");
        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->component('Projects/Dashboard')
            ->where('progress', 50)
            ->where('total_checklists', 2)
            ->where('done_checklists', 1)
            ->where('spent_budget', 4000000)
            ->where('project.total_budget', 50000000)
        );
    }
}
