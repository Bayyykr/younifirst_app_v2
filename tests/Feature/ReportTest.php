<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_access_reports_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reports'));

        $response->assertOk();
    }

    public function test_admin_can_upload_signature(): void
    {
        $admin = User::factory()->admin()->create();
        $file = UploadedFile::fake()->image('signature.png');

        $response = $this->actingAs($admin)->post(route('admin.reports.upload-signature'), [
            'signature' => $file,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
        ]);
        
        $data = $response->json();
        $this->assertNotNull($data['url']);
    }

    public function test_admin_can_delete_signature(): void
    {
        $admin = User::factory()->admin()->create();
        
        // Upload first
        $file = UploadedFile::fake()->image('signature.png');
        $this->actingAs($admin)->post(route('admin.reports.upload-signature'), [
            'signature' => $file,
        ]);

        // Then delete
        $response = $this->actingAs($admin)->delete(route('admin.reports.delete-signature'));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'message' => 'Tanda tangan berhasil dihapus.',
        ]);
    }

    public function test_admin_can_export_users_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.export'), [
            'type' => 'users',
            'format' => 'pdf',
            'status_akun' => 'all',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_export_teams_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        
        // Create user members
        $memberUser1 = User::factory()->create();
        $memberUser2 = User::factory()->create();

        // Create a team with a report
        $team = Team::factory()->create([
            'competition_level' => 'nasional',
            'achievement_rank' => 'Juara 1',
        ]);

        // Add members to team
        TeamMember::factory()->create([
            'team_id' => $team->team_id,
            'user_id' => $memberUser1->user_id,
            'role' => 'leader',
            'status' => 'active',
        ]);

        TeamMember::factory()->create([
            'team_id' => $team->team_id,
            'user_id' => $memberUser2->user_id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.export'), [
            'type' => 'teams',
            'format' => 'pdf',
            'tingkat_lomba' => 'nasional',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
