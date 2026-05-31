<?php

namespace Tests\Feature\Api;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\LostfoundComment;
use App\Models\LostfoundItem;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class MobileApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        $this->mock(FirebaseService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('signInWithEmailAndPassword')
                ->andReturn(['uid' => 'firebase-uid-api'])
                ->byDefault();
            $mock->shouldReceive('updateUserPassword')->andReturnTrue()->byDefault();
            $mock->shouldReceive('createUser')->andReturn('firebase-created-uid')->byDefault();
            $mock->shouldReceive('getDatabase')->andReturnNull()->byDefault();
            $mock->shouldReceive('sendNotification')->andReturnTrue()->byDefault();
        });
    }

    public function test_api_login_returns_sanctum_token(): void
    {
        $user = User::factory()->regularUser()->create([
            'firebase_uid' => 'firebase-uid-api',
            'status' => 'active',
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => false,
            'device_name' => 'phpunit',
            'fcm_token' => 'fcm-token-test',
        ])->assertOk()
            ->assertJsonPath('message', 'Login sukses')
            ->assertJsonStructure(['data' => ['user', 'token', 'token_type']]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'fcm_token' => 'fcm-token-test',
        ]);
    }

    public function test_protected_api_requires_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_authenticated_user_can_update_profile_fcm_and_password(): void
    {
        $user = User::factory()->regularUser()->create([
            'firebase_uid' => 'firebase-uid-api',
            'status' => 'active',
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/users/fcm-token', [
            'fcm_token' => 'new-token',
        ])->assertOk()->assertJsonPath('message', 'FCM token updated successfully');

        $this->postJson('/api/users/profile', [
            'name' => 'Nama API Update',
            'nim' => 'E41240099',
            'prodi' => 'Sistem Informasi',
        ])->assertOk()->assertJsonPath('data.name', 'Nama API Update');

        $this->postJson('/api/change-password', [
            'current_password' => 'password',
            'new_password' => 'new-password-api',
            'new_password_confirmation' => 'new-password-api',
        ])->assertOk()->assertJsonPath('message', 'Password berhasil diubah.');
    }

    public function test_event_api_lists_shows_creates_updates_likes_and_deletes_events(): void
    {
        $user = User::factory()->regularUser()->create(['status' => 'active']);
        $category = EventCategory::create([
            'category_id' => 1,
            'name_category' => 'Workshop',
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/events/add', [
            'category_id' => $category->category_id,
            'title' => 'Event API',
            'description' => 'Deskripsi event API.',
            'start_date' => now()->addDay()->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
            'location' => 'Gedung API',
            'created_by' => $user->user_id,
            'status' => 'pending',
        ])->assertStatus(213)->assertJsonPath('message', 'Event created successfully');

        $event = Event::where('title', 'Event API')->firstOrFail();

        $this->getJson('/api/events?search=Event API')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->getJson("/api/events/{$event->event_id}")
            ->assertOk()
            ->assertJsonPath('data.event_id', $event->event_id);

        $this->putJson("/api/events/{$event->event_id}", [
            'title' => 'Event API Updated',
            'status' => 'upcoming',
        ])->assertOk()->assertJsonPath('data.title', 'Event API Updated');

        $this->postJson("/api/events/{$event->event_id}/like")
            ->assertOk()
            ->assertJsonPath('status', 'liked')
            ->assertJsonPath('likes_count', 1);

        $this->getJson("/api/events/{$event->event_id}/likes")
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->postJson("/api/events/{$event->event_id}/like")
            ->assertOk()
            ->assertJsonPath('status', 'unliked')
            ->assertJsonPath('likes_count', 0);

        $this->deleteJson("/api/events/{$event->event_id}")
            ->assertOk()
            ->assertJsonPath('message', 'Event deleted (soft) successfully');

        $this->assertSoftDeleted('events', ['event_id' => $event->event_id]);
    }

    public function test_team_api_handles_create_join_respond_report_and_delete_flow(): void
    {
        $leader = User::factory()->regularUser()->create(['status' => 'active']);
        $memberUser = User::factory()->regularUser()->create(['status' => 'active']);

        Sanctum::actingAs($leader);
        $this->postJson('/api/teams/add', [
            'team_name' => 'Team API',
            'competition_name' => 'Hackathon API',
            'description' => 'Deskripsi tim API.',
            'max_member' => 5,
        ])->assertStatus(211)->assertJsonPath('message', 'Team created successfully and waiting for admin approval');

        $team = Team::where('team_name', 'Team API')->firstOrFail();
        $team->update(['status' => 'approved']);
        TeamMember::where('team_id', $team->team_id)
            ->where('role', 'leader')
            ->update(['status' => 'active']);

        $this->getJson('/api/teams')
            ->assertOk()
            ->assertJsonStructure(['data']);
        $this->getJson("/api/teams/{$team->team_id}")
            ->assertOk()
            ->assertJsonPath('data.team_id', $team->team_id);

        Sanctum::actingAs($memberUser);
        $this->postJson("/api/teams/{$team->team_id}/join", [
            'proposed_role' => 'Backend Developer',
            'description' => 'Saya ingin bergabung.',
        ])->assertCreated()->assertJsonPath('message', 'Join request sent successfully. Waiting for leader approval.');

        $pendingMember = TeamMember::where('team_id', $team->team_id)
            ->where('user_id', $memberUser->user_id)
            ->firstOrFail();

        Sanctum::actingAs($leader);
        $this->getJson("/api/teams/{$team->team_id}/applications")
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->postJson("/api/teams/{$team->team_id}/members/{$pendingMember->member_id}/respond", [
            'action' => 'accept',
        ])->assertOk()->assertJsonPath('data.status', 'active');

        $this->postJson("/api/teams/{$team->team_id}/report", [
            'achievement_rank' => 'Juara 2',
            'competition_level' => 'regional',
        ])->assertOk()->assertJsonPath('data.achievement_rank', 'Juara 2');

        $this->deleteJson("/api/teams/{$team->team_id}")
            ->assertOk()
            ->assertJsonPath('message', 'Team deleted successfully');

        $this->assertSoftDeleted('teams', ['team_id' => $team->team_id]);
    }

    public function test_lostfound_api_handles_item_and_comment_flow(): void
    {
        $user = User::factory()->regularUser()->create(['status' => 'active']);
        $otherUser = User::factory()->regularUser()->create(['status' => 'active']);
        Sanctum::actingAs($user);

        $this->postJson('/api/lostfound/add', [
            'item_name' => 'Kunci API',
            'description' => 'Kunci ditemukan via API.',
            'location' => 'Perpustakaan',
            'status' => 'found',
        ])->assertCreated()->assertJsonPath('message', 'Lost/Found item created successfully');

        $item = LostfoundItem::where('item_name', 'Kunci API')->firstOrFail();

        $this->getJson('/api/lostfound?search=Kunci')
            ->assertOk()
            ->assertJsonStructure(['data']);
        $this->getJson("/api/lostfound/{$item->lostfound_id}")
            ->assertOk()
            ->assertJsonPath('data.lostfound_id', $item->lostfound_id);

        $this->putJson("/api/lostfound/{$item->lostfound_id}", [
            'status' => 'claimed',
        ])->assertOk()->assertJsonPath('data.status', 'claimed');

        Sanctum::actingAs($otherUser);
        $this->postJson("/api/lostfound/{$item->lostfound_id}/comments", [
            'comment' => 'Saya melihat barang ini.',
        ])->assertCreated()->assertJsonPath('message', 'Comment added successfully');

        $comment = LostfoundComment::where('lostfound_id', $item->lostfound_id)->firstOrFail();

        $this->getJson("/api/lostfound/{$item->lostfound_id}/comments")
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->putJson("/api/lostfound/comments/{$comment->comment_id}", [
            'comment' => 'Komentar diperbarui.',
        ])->assertOk()->assertJsonPath('data.comment', 'Komentar diperbarui.');

        $this->deleteJson("/api/lostfound/comments/{$comment->comment_id}")
            ->assertOk()
            ->assertJsonPath('message', 'Comment deleted successfully');

        Sanctum::actingAs($user);
        $this->deleteJson("/api/lostfound/{$item->lostfound_id}")
            ->assertOk()
            ->assertJsonPath('message', 'Lost/Found item deleted successfully');

        $this->assertSoftDeleted('lostfound_items', ['lostfound_id' => $item->lostfound_id]);
    }

    public function test_announcement_api_lists_and_shows_published_announcements(): void
    {
        $admin = User::factory()->admin()->create(['status' => 'active']);
        $announcement = Announcement::factory()->create([
            'title' => 'Pengumuman API Publish',
            'status' => 'publish',
            'publish_at' => now()->subMinute(),
            'created_by' => $admin->user_id,
        ]);
        Announcement::factory()->create([
            'title' => 'Pengumuman API Draft',
            'status' => 'draft',
            'created_by' => $admin->user_id,
        ]);

        Sanctum::actingAs(User::factory()->regularUser()->create(['status' => 'active']));

        $this->getJson('/api/announcements?search=Publish')
            ->assertOk()
            ->assertJsonFragment(['announcement_id' => $announcement->announcement_id])
            ->assertJsonMissing(['title' => 'Pengumuman API Draft']);

        $this->getJson("/api/announcements/{$announcement->announcement_id}")
            ->assertOk()
            ->assertJsonPath('data.announcement_id', $announcement->announcement_id);
    }
}
