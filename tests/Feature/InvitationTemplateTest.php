<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InvitationTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_invitation_template(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Romeo & Juliet Wedding',
            'slug' => 'romeo-juliet-wedding',
            'wedding_date' => '2026-11-11',
            'total_budget' => 50000000.00,
            'invitation_template' => 'romantic-luxury',
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);

        // Update to Minang Traditional template
        $response = $this->post("/projects/{$project->id}/invitation", [
            'invitation_template' => 'minang-traditional',
            'groom_name' => 'Romeo Minang',
            'bride_name' => 'Juliet Minang',
        ]);

        $response->assertSessionHasNoErrors();
        $project->refresh();
        $this->assertEquals('minang-traditional', $project->invitation_template);
        $this->assertTrue($project->is_published);

        // Update to Botanical Nature template
        $response = $this->post("/projects/{$project->id}/invitation", [
            'invitation_template' => 'botanical-nature',
            'groom_name' => 'Romeo Nature',
            'bride_name' => 'Juliet Nature',
        ]);

        $response->assertSessionHasNoErrors();
        $project->refresh();
        $this->assertEquals('botanical-nature', $project->invitation_template);

        // Update to Classic Royal template
        $response = $this->post("/projects/{$project->id}/invitation", [
            'invitation_template' => 'classic-royal',
            'groom_name' => 'Romeo Royal',
            'bride_name' => 'Juliet Royal',
        ]);

        $response->assertSessionHasNoErrors();
        $project->refresh();
        $this->assertEquals('classic-royal', $project->invitation_template);
    }

    public function test_invalid_template_is_rejected(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Test Invalid Wedding',
            'slug' => 'test-invalid-wedding',
            'wedding_date' => '2026-11-11',
            'total_budget' => 50000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);

        $response = $this->post("/projects/{$project->id}/invitation", [
            'invitation_template' => 'invalid-alien-template',
        ]);

        $response->assertSessionHasErrors(['invitation_template']);
    }

    public function test_public_invitation_renders_selected_template(): void
    {
        $project = Project::create([
            'name' => 'Royal Couple Wedding',
            'slug' => 'royal-couple-wedding',
            'wedding_date' => '2026-12-25',
            'total_budget' => 100000000.00,
            'invitation_template' => 'classic-royal',
            'groom_name' => 'Pangeran William',
            'bride_name' => 'Putri Kate',
            'is_published' => true,
        ]);

        $guest = Guest::create([
            'project_id' => $project->id,
            'name' => 'Bapak Joko',
            'side' => 'pria',
            'pax' => 2,
        ]);

        $response = $this->get("/undangan/{$project->slug}?to=Bapak+Joko");
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('PublicInvitation')
            ->has('project', fn (Assert $p) => $p
                ->where('slug', 'royal-couple-wedding')
                ->where('invitation_template', 'classic-royal')
                ->where('groom_name', 'Pangeran William')
                ->where('bride_name', 'Putri Kate')
                ->etc()
            )
        );
    }
}
