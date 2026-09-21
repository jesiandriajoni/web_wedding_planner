<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\Guest;
use App\Models\Project;
use App\Models\Rundown;
use App\Models\SeserahanItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_relations_and_cascade_delete(): void
    {
        $user = User::factory()->create();

        // Create Project
        $project = Project::create([
            'name' => 'John & Doe Wedding',
            'slug' => 'john-doe-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);

        // Connect user with role
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        // Create Child Items
        $checklist = Checklist::create([
            'project_id' => $project->id,
            'title' => 'Book Venue',
            'status' => 'pending',
            'assigned_to' => 'WO',
        ]);

        $guest = Guest::create([
            'project_id' => $project->id,
            'name' => 'Alice Smith',
            'side' => 'bersama',
            'rsvp' => 'pending',
            'pax' => 2,
            'guest_book_message' => 'Congrats!',
        ]);

        $vendor = Vendor::create([
            'project_id' => $project->id,
            'name' => 'Luxury Catering',
            'category' => 'Catering',
            'contact' => '08123456789',
            'package_price' => 20000000.00,
            'paid_amount' => 5000000.00,
            'status' => 'dp',
            'mou_path' => 'contracts/catering.pdf',
        ]);

        $rundown = Rundown::create([
            'project_id' => $project->id,
            'time' => '08:00',
            'activity' => 'Akad Nikah',
            'description' => 'Holy matrimony',
            'assigned_to' => 'Penghulu',
        ]);

        $seserahan = SeserahanItem::create([
            'project_id' => $project->id,
            'item_name' => 'Hantaran Emas',
            'status' => 'pending',
            'tracking_url' => null,
            'price' => 10000000.00,
        ]);

        // Assert relations
        $this->assertEquals(1, $project->users()->count());
        $this->assertEquals('pengantin', $project->users()->first()->pivot->role);
        $this->assertEquals(1, $project->checklists()->count());
        $this->assertEquals(1, $project->guests()->count());
        $this->assertEquals(1, $project->vendors()->count());
        $this->assertEquals(1, $project->rundowns()->count());
        $this->assertEquals(1, $project->seserahanItems()->count());

        // Test cascade deletes
        $project->delete();

        // Check DB child tables are empty
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('project_user', 0);
        $this->assertDatabaseCount('checklists', 0);
        $this->assertDatabaseCount('guests', 0);
        $this->assertDatabaseCount('vendors', 0);
        $this->assertDatabaseCount('rundowns', 0);
        $this->assertDatabaseCount('seserahan_items', 0);
    }
}
