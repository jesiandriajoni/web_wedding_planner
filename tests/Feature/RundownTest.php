<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Rundown;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RundownTest extends TestCase
{
    use RefreshDatabase;

    public function test_rundown_crud_requires_auth_and_membership(): void
    {
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);

        $response = $this->get("/projects/{$project->id}/rundowns");
        $response->assertRedirect('/login');
    }

    public function test_rundown_time_validation_works(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'wo']);

        $this->actingAs($user);

        // Try adding with invalid time format
        $response = $this->post("/projects/{$project->id}/rundowns", [
            'time' => 'pagi-pagi',
            'activity' => 'Akad Nikah',
            'description' => 'Sesi akad',
            'assigned_to' => 'WO Team',
        ]);

        $response->assertSessionHasErrors(['time']);
    }

    public function test_rundown_crud_and_pdf_export(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'wo']);

        $this->actingAs($user);

        // 1. Create Rundown
        $response = $this->post("/projects/{$project->id}/rundowns", [
            'time' => '08:00',
            'activity' => 'Akad Nikah',
            'description' => 'Sesi akad nikah sakral',
            'assigned_to' => 'Penghulu',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('rundowns', ['activity' => 'Akad Nikah', 'time' => '08:00']);

        $rundown = Rundown::where('activity', 'Akad Nikah')->first();

        // 2. Update Rundown
        $responseUp = $this->put("/projects/{$project->id}/rundowns/{$rundown->id}", [
            'time' => '08:30',
            'activity' => 'Akad Nikah Selesai',
            'description' => 'Sesi foto bersama',
            'assigned_to' => 'Photographer',
        ]);
        $responseUp->assertRedirect();
        $this->assertDatabaseHas('rundowns', ['id' => $rundown->id, 'time' => '08:30', 'activity' => 'Akad Nikah Selesai']);

        // 3. Export PDF
        $responsePdf = $this->get("/projects/{$project->id}/rundown/pdf");
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('Content-Type', 'application/pdf');
    }
}
