<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class GuestImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::create([
            'name' => 'Import Test Wedding',
            'slug' => 'import-test-wedding',
            'wedding_date' => '2026-12-12',
            'total_budget' => 50000000.00,
        ]);
        $this->project->users()->attach($this->user->id, ['role' => 'pengantin']);
    }

    public function test_user_can_download_guest_template_xlsx(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('projects.guests.template', [
            'project' => $this->project->id,
            'format' => 'xlsx',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_user_can_download_guest_template_csv(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('projects.guests.template', [
            'project' => $this->project->id,
            'format' => 'csv',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_user_can_import_guests_from_csv(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Nama Tamu,Pihak,Pax\n"
            ."Andi Pratama,pria,2\n"
            ."Bella Safitri,wanita,3\n"
            ."Keluarga Besar Hartono,bersama,5\n";

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csvContent);

        $response = $this->post(route('projects.guests.import', $this->project->id), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'project_id' => $this->project->id,
            'name' => 'Andi Pratama',
            'side' => 'pria',
            'pax' => 2,
            'rsvp' => 'pending',
        ]);

        $this->assertDatabaseHas('guests', [
            'project_id' => $this->project->id,
            'name' => 'Bella Safitri',
            'side' => 'wanita',
            'pax' => 3,
        ]);

        $this->assertDatabaseHas('guests', [
            'project_id' => $this->project->id,
            'name' => 'Keluarga Besar Hartono',
            'side' => 'bersama',
            'pax' => 5,
        ]);

        $this->assertEquals(3, Guest::where('project_id', $this->project->id)->count());
    }

    public function test_user_can_import_guests_from_xlsx(): void
    {
        $this->actingAs($this->user);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_guest_').'.xlsx';
        $writer = new XlsxWriter;
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues(['Nama Tamu', 'Pihak', 'Jumlah Pax']));
        $writer->addRow(Row::fromValues(['Citra Kirana', 'wanita', 2]));
        $writer->addRow(Row::fromValues(['Doni Salman', 'pria', 1]));
        $writer->close();

        $file = new UploadedFile($tempPath, 'guests.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('projects.guests.import', $this->project->id), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'project_id' => $this->project->id,
            'name' => 'Citra Kirana',
            'side' => 'wanita',
            'pax' => 2,
        ]);

        $this->assertDatabaseHas('guests', [
            'project_id' => $this->project->id,
            'name' => 'Doni Salman',
            'side' => 'pria',
            'pax' => 1,
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }

    public function test_guest_import_applies_defaults_for_missing_fields(): void
    {
        $this->actingAs($this->user);

        // No side, no pax specified
        $csvContent = "Nama Tamu,Pihak,Pax\n"
            ."Eko Patrio,,\n";

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csvContent);

        $response = $this->post(route('projects.guests.import', $this->project->id), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('guests', [
            'project_id' => $this->project->id,
            'name' => 'Eko Patrio',
            'side' => 'bersama',
            'pax' => 2,
            'rsvp' => 'pending',
        ]);
    }

    public function test_unauthorized_user_cannot_import_guests(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $file = UploadedFile::fake()->createWithContent('guests.csv', "Nama Tamu\nFarhan");

        $response = $this->post(route('projects.guests.import', $this->project->id), [
            'file' => $file,
        ]);

        $response->assertForbidden();
    }
}
