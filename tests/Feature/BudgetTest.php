<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_page_requires_auth_and_member(): void
    {
        $project = Project::create([
            'name' => 'John Wedding',
            'slug' => 'john-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 100000000.00,
        ]);

        $response = $this->get("/projects/{$project->id}/budget");
        $response->assertRedirect('/login');
    }

    public function test_budget_allocations_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'John Wedding',
            'slug' => 'john-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 100000000.00, // 100 million
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);
        $response = $this->get("/projects/{$project->id}/budget");
        $response->assertStatus(200);

        // Assert percentage-based allocations
        $response->assertInertia(fn ($page) => $page
            ->component('Projects/Budget')
            ->where('allocations.vendor', 50000000)
            ->where('allocations.katering', 30000000)
            ->where('allocations.seserahan', 10000000)
            ->where('allocations.lainnya', 10000000)
        );
    }

    public function test_payment_updates_vendor_paid_amount_and_status(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'John Wedding',
            'slug' => 'john-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 100000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $vendor = Vendor::create([
            'project_id' => $project->id,
            'name' => 'Catering VIP',
            'category' => 'Katering',
            'contact' => '081',
            'package_price' => 30000000.00,
            'paid_amount' => 0.00,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        // Add DP Payment
        $response1 = $this->post("/projects/{$project->id}/vendors/{$vendor->id}/payments", [
            'amount' => 10000000.00,
            'notes' => 'DP 1',
        ]);
        $response1->assertRedirect();

        // Refresh vendor
        $vendor->refresh();
        $this->assertEquals(10000000.00, (float) $vendor->paid_amount);
        $this->assertEquals('dp', $vendor->status);

        // Add Pelunasan Payment
        $response2 = $this->post("/projects/{$project->id}/vendors/{$vendor->id}/payments", [
            'amount' => 20000000.00,
            'notes' => 'Pelunasan',
        ]);
        $response2->assertRedirect();

        // Refresh vendor
        $vendor->refresh();
        $this->assertEquals(30000000.00, (float) $vendor->paid_amount);
        $this->assertEquals('paid', $vendor->status);

        // Verify database counts
        $this->assertDatabaseCount('payments', 2);
    }
}
