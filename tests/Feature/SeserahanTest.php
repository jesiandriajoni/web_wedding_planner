<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\SeserahanItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeserahanTest extends TestCase
{
    use RefreshDatabase;

    public function test_seserahan_requires_auth_and_membership(): void
    {
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00
        ]);

        $response = $this->get("/projects/{$project->id}/seserahan");
        $response->assertRedirect('/login');
    }

    public function test_seserahan_url_and_status_validation(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00
        ]);
        $project->users()->attach($user->id, ['role' => 'wo']);

        $this->actingAs($user);

        // Test 1: Reject malicious protocol (XSS/Open Redirect)
        $response1 = $this->post("/projects/{$project->id}/seserahan", [
            'name' => 'Cincin Kawin',
            'status' => 'pending',
            'tracking_url' => 'javascript:alert(1)'
        ]);
        $response1->assertSessionHasErrors(['tracking_url']);

        // Test 2: Reject invalid status
        $response2 = $this->post("/projects/{$project->id}/seserahan", [
            'name' => 'Cincin Kawin',
            'status' => 'completed_state',
            'tracking_url' => 'https://jne.co.id/tracking/123'
        ]);
        $response2->assertSessionHasErrors(['status']);
    }

    public function test_seserahan_crud_operations(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00
        ]);
        $project->users()->attach($user->id, ['role' => 'wo']);

        $this->actingAs($user);

        // 1. Create Seserahan
        $response = $this->post("/projects/{$project->id}/seserahan", [
            'name' => 'Tas Kulit',
            'status' => 'pending',
            'tracking_url' => 'https://jne.co.id/tracking/123'
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('seserahan_items', ['item_name' => 'Tas Kulit', 'status' => 'pending']);

        $item = SeserahanItem::where('item_name', 'Tas Kulit')->first();

        // 2. Update Status/Item
        $responseUp = $this->put("/projects/{$project->id}/seserahan/{$item->id}", [
            'name' => 'Tas Kulit Mewah',
            'status' => 'purchased',
            'tracking_url' => 'https://jne.co.id/tracking/123456'
        ]);
        $responseUp->assertRedirect();
        $this->assertDatabaseHas('seserahan_items', ['id' => $item->id, 'item_name' => 'Tas Kulit Mewah', 'status' => 'purchased']);

        // 3. Delete Item
        $responseDel = $this->delete("/projects/{$project->id}/seserahan/{$item->id}");
        $responseDel->assertRedirect();
        $this->assertDatabaseMissing('seserahan_items', ['id' => $item->id]);
    }
}
