<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_page_requires_auth_and_membership(): void
    {
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);

        $response = $this->get("/projects/{$project->id}/guide");
        $response->assertRedirect('/login');
    }

    public function test_guide_page_accessible_by_member(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);
        $response = $this->get("/projects/{$project->id}/guide");
        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->component('Projects/Guide')
            ->where('project.id', $project->id)
        );
    }
}
