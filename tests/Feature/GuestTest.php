<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_crud_requires_auth_and_membership(): void
    {
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00
        ]);

        $response = $this->get("/projects/{$project->id}/guests");
        $response->assertRedirect('/login');
    }

    public function test_guest_crud_operations_work_properly(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);

        // 1. Create Guest
        $response = $this->post("/projects/{$project->id}/guests", [
            'name' => 'Ahmad',
            'side' => 'pria',
            'rsvp' => 'pending',
            'pax' => 2
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('guests', ['name' => 'Ahmad', 'side' => 'pria', 'pax' => 2]);

        $guest = Guest::where('name', 'Ahmad')->first();

        // 2. Update Guest
        $responseUp = $this->put("/projects/{$project->id}/guests/{$guest->id}", [
            'name' => 'Ahmad Fauzi',
            'side' => 'bersama',
            'rsvp' => 'pending',
            'pax' => 4
        ]);
        $responseUp->assertRedirect();
        $this->assertDatabaseHas('guests', ['id' => $guest->id, 'name' => 'Ahmad Fauzi', 'side' => 'bersama', 'pax' => 4]);

        // 3. Delete Guest
        $responseDel = $this->delete("/projects/{$project->id}/guests/{$guest->id}");
        $responseDel->assertRedirect();
        $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
    }

    public function test_public_rsvp_only_allows_safe_updates_and_prevents_mass_assignment(): void
    {
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00
        ]);

        $guest = Guest::create([
            'project_id' => $project->id,
            'name' => 'Ahmad',
            'side' => 'pria',
            'rsvp' => 'pending',
            'pax' => 2
        ]);

        // Submit RSVP publicly
        $response = $this->post("/undangan/{$project->slug}/rsvp", [
            'guest_id' => $guest->id,
            'rsvp' => 'hadir',
            'pax' => 3,
            'guest_book_message' => 'Happy wedding!',
            // Attemped mass assignment: trying to change side or name
            'side' => 'wanita',
            'name' => 'Hacker Name'
        ]);

        $response->assertRedirect();

        $guest->refresh();
        $this->assertEquals('hadir', $guest->rsvp);
        $this->assertEquals(3, $guest->pax);
        $this->assertEquals('Happy wedding!', $guest->guest_book_message);
        
        // Assert malicious inputs were blocked and kept original values
        $this->assertEquals('pria', $guest->side);
        $this->assertEquals('Ahmad', $guest->name);
    }
}
