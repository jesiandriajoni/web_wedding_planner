<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'pengantin',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Pengantin User',
            'email' => '    ',
            'role' => 'pengantin',
        ]);

        $project = \App\Models\Project::create([
            'name' => 'Rose Wedding Sample',
            'slug' => 'rose-wedding-sample',
            'wedding_date' => '2026-10-10',
            'total_budget' => 150000000.00
        ]);

        $project->users()->attach($user->id, ['role' => 'pengantin']);
    }
}
