<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Wedding',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun Pengantin
        $pengantin = User::updateOrCreate(
            ['email' => 'pengantin@example.com'],
            [
                'name' => 'Pengantin User',
                'password' => Hash::make('password'),
                'role' => 'pengantin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Project Sample
        $project = Project::firstOrCreate(
            ['slug' => 'rose-wedding-sample'],
            [
                'name' => 'Rose Wedding Sample',
                'wedding_date' => '2026-10-10',
                'total_budget' => 150000000.00,
                'groom_name' => 'Romeo',
                'bride_name' => 'Juliet',
                'invitation_template' => 'romantic-luxury',
                'is_published' => true,
            ]
        );

        if (! $project->users()->where('user_id', $pengantin->id)->exists()) {
            $project->users()->attach($pengantin->id, ['role' => 'pengantin']);
        }
    }
}
