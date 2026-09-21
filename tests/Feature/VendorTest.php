<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_crud_and_mou_upload(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Rose Wedding',
            'slug' => 'rose-wedding',
            'wedding_date' => '2026-10-10',
            'total_budget' => 50000000.00,
        ]);
        $project->users()->attach($user->id, ['role' => 'pengantin']);

        $this->actingAs($user);

        // 1. Create Vendor with upload
        $file = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $response = $this->post("/projects/{$project->id}/vendors", [
            'name' => 'Vibrant Decor',
            'category' => 'Dekorasi',
            'contact' => '08123',
            'package_price' => 15000000.00,
            'mou' => $file,
        ]);

        $response->assertRedirect();

        $vendor = Vendor::where('name', 'Vibrant Decor')->first();
        $this->assertNotNull($vendor);
        $this->assertEquals(15000000.00, (float) $vendor->package_price);
        $this->assertNotNull($vendor->mou_path);

        // Assert file exists in storage
        Storage::disk('public')->assertExists($vendor->mou_path);

        // 2. Reject malicious upload (e.g. php file)
        $maliciousFile = UploadedFile::fake()->create('malicious.php', 10, 'text/x-php');
        $responseBad = $this->post("/projects/{$project->id}/vendors", [
            'name' => 'Bad Vendor',
            'category' => 'Music',
            'contact' => '08123',
            'package_price' => 5000000.00,
            'mou' => $maliciousFile,
        ]);
        $responseBad->assertSessionHasErrors(['mou']);

        // 3. Delete Vendor deletes file
        $mouPath = $vendor->mou_path;
        $responseDel = $this->delete("/projects/{$project->id}/vendors/{$vendor->id}");
        $responseDel->assertRedirect();

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
        Storage::disk('public')->assertMissing($mouPath);
    }
}
