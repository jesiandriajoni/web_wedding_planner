<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_route_requires_authentication(): void
    {
        $response = $this->get('/projects');
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_access_project_they_do_not_own(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $project = Project::create([
            'name' => 'Alice & Bob Wedding',
            'slug' => 'alice-bob-wedding',
            'wedding_date' => '2026-08-08',
            'total_budget' => 40000000.00,
        ]);

        $project->users()->attach($userB->id, ['role' => 'pengantin']);

        // User A tries to view Project B's details
        $this->actingAs($userA);
        $response = $this->get("/projects/{$project->id}");
        $response->assertStatus(403);
    }

    public function test_user_can_access_project_they_are_member_of(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Alice & Bob Wedding',
            'slug' => 'alice-bob-wedding',
            'wedding_date' => '2026-08-08',
            'total_budget' => 40000000.00,
        ]);

        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);
        $response = $this->get("/projects/{$project->id}");
        $response->assertStatus(200);
    }

    public function test_duplicate_project_names_generate_unique_slugs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create first project
        $response1 = $this->post('/projects', [
            'name' => 'Same Name Wedding',
            'wedding_date' => '2026-08-08',
            'total_budget' => 40000000.00,
            'pengantin_name' => 'Pengantin Satu',
            'pengantin_email' => 'pengantin1@example.com',
            'pengantin_password' => 'password123',
        ]);
        $project1 = Project::where('name', 'Same Name Wedding')->first();
        $this->assertNotNull($project1);
        $this->assertEquals('same-name-wedding', $project1->slug);

        // Create second project with same name
        $response2 = $this->post('/projects', [
            'name' => 'Same Name Wedding',
            'wedding_date' => '2026-09-09',
            'total_budget' => 50000000.00,
            'pengantin_name' => 'Pengantin Dua',
            'pengantin_email' => 'pengantin2@example.com',
            'pengantin_password' => 'password123',
        ]);

        $project2 = Project::where('wedding_date', '2026-09-09')->first();
        $this->assertNotNull($project2);
        $this->assertNotEquals($project1->slug, $project2->slug);
        $this->assertStringContainsString('same-name-wedding-', $project2->slug);
    }

    public function test_public_invitation_route_accessible_without_auth(): void
    {
        $project = Project::create([
            'name' => 'Charlie & Delta Wedding',
            'slug' => 'charlie-delta-wedding',
            'wedding_date' => '2026-12-12',
            'total_budget' => 40000000.00,
            'is_published' => true,
        ]);

        $response = $this->get("/undangan/{$project->slug}");
        $response->assertStatus(200);
    }
}
