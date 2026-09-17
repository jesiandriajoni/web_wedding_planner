<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $pengantin = User::factory()->create(['role' => 'pengantin']);

        $this->actingAs($pengantin);

        $response = $this->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'pengantin', 'name' => 'Pengantin User']);

        $this->actingAs($admin);

        $response = $this->get('/admin/users');
        $response->assertStatus(200);
    }
}
