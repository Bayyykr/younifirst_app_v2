<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\LostfoundComment;
use App\Models\LostfoundItem;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\AnnouncementNotificationService;
use App\Services\FirebaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake("public");

        $this->mock(FirebaseService::class, function (
            MockInterface $mock,
        ): void {
            $mock
                ->shouldReceive("createUser")
                ->andReturn("firebase-uid-test")
                ->byDefault();
            $mock
                ->shouldReceive("sendNotification")
                ->andReturnTrue()
                ->byDefault();
            $mock->shouldReceive("getDatabase")->andReturnNull()->byDefault();
        });

        $this->mock(AnnouncementNotificationService::class, function (
            MockInterface $mock,
        ): void {
            $mock->shouldReceive("publishAndNotify")->andReturn(0)->byDefault();
        });
    }

    public function test_admin_can_create_update_and_delete_announcement(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route("admin.announcement.store"), [
                "title" => "Pengumuman Testing",
                "content" => "Konten pengumuman untuk testing.",
                "status" => "publish",
            ])
            ->assertRedirect();

        $announcement = Announcement::where(
            "title",
            "Pengumuman Testing",
        )->firstOrFail();
        $this->assertSame($admin->user_id, $announcement->created_by);

        $this->actingAs($admin)
            ->put(
                route(
                    "admin.announcement.update",
                    $announcement->announcement_id,
                ),
                [
                    "title" => "Pengumuman Testing Update",
                    "content" => "Konten sudah diperbarui.",
                    "status" => "draft",
                ],
            )
            ->assertRedirect();

        $this->assertDatabaseHas("announcements", [
            "announcement_id" => $announcement->announcement_id,
            "title" => "Pengumuman Testing Update",
            "status" => "draft",
        ]);

        $this->actingAs($admin)
            ->delete(
                route(
                    "admin.announcement.destroy",
                    $announcement->announcement_id,
                ),
            )
            ->assertRedirect();

        $this->assertSoftDeleted("announcements", [
            "announcement_id" => $announcement->announcement_id,
        ]);
    }

    public function test_admin_can_create_update_approve_reject_and_delete_events(): void
    {
        $admin = User::factory()->admin()->create();
        $category = EventCategory::create([
            "category_id" => 1,
            "name_category" => "Seminar",
        ]);

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->postJson(route("admin.events.store"), [
                "category_id" => $category->category_id,
                "title" => "Event Admin Testing",
                "description" => "Deskripsi event admin testing.",
                "start_date" => now()->addDay()->format("Y-m-d"),
                "start_time" => "09:00",
                "end_date" => now()->addDay()->format("Y-m-d"),
                "end_time" => "11:00",
                "location" => "Aula Kampus",
            ])
            ->assertOk()
            ->assertJsonPath("message", "Event created successfully.");

        $event = Event::where("title", "Event Admin Testing")->firstOrFail();

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->putJson(route("admin.events.update", $event->event_id), [
                "category_id" => $category->category_id,
                "title" => "Event Admin Updated",
                "description" => "Deskripsi event sudah diperbarui.",
                "start_date" => now()->addDays(2)->format("Y-m-d"),
                "start_time" => "10:00",
                "end_date" => now()->addDays(2)->format("Y-m-d"),
                "end_time" => "12:00",
                "location" => "Auditorium",
            ])
            ->assertOk()
            ->assertJsonPath("message", "Event updated successfully.");

        $this->assertDatabaseHas("events", [
            "event_id" => $event->event_id,
            "title" => "Event Admin Updated",
            "location" => "Auditorium",
        ]);

        $event->update(["status" => "pending"]);

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->postJson(route("admin.events.respond", $event->event_id), [
                "action" => "approve",
            ])
            ->assertOk()
            ->assertJsonPath("data.status", "upcoming");

        $event->update(["status" => "pending"]);

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->postJson(route("admin.events.respond", $event->event_id), [
                "action" => "reject",
                "rejection_reason" => "Data belum lengkap.",
            ])
            ->assertOk()
            ->assertJsonPath("data.status", "rejected");

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->deleteJson(route("admin.events.destroy", $event->event_id))
            ->assertOk()
            ->assertJsonPath("message", "Event deleted successfully.");

        $this->assertSoftDeleted("events", [
            "event_id" => $event->event_id,
        ]);
    }

    public function test_admin_can_manage_users_and_suspensions(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->postJson(route("admin.users.store"), [
                "name" => "User Testing",
                "email" => "user-testing@example.com",
                "password" => "password123",
                "role" => "user",
                "nim" => "E41240001",
                "prodi" => "Teknik Informatika",
                "status" => "active",
            ])
            ->assertCreated()
            ->assertJsonPath("message", "User created successfully");

        $user = User::where("email", "user-testing@example.com")->firstOrFail();

        $this->actingAs($admin)
            ->putJson(route("admin.users.update", $user->user_id), [
                "name" => "User Testing Suspended",
                "status" => "suspended",
                "duration" => "7",
                "reason" => "Pelanggaran aturan",
                "notes" => "Catatan internal",
            ])
            ->assertOk()
            ->assertJsonPath("message", "User updated successfully");

        $this->assertDatabaseHas("users", [
            "user_id" => $user->user_id,
            "name" => "User Testing Suspended",
            "status" => "suspended",
        ]);
        $this->assertDatabaseHas("user_suspensions", [
            "user_id" => $user->user_id,
            "duration" => "7",
            "reason" => "Pelanggaran aturan",
        ]);

        $this->actingAs($admin)
            ->deleteJson(route("admin.users.destroy", $user->user_id))
            ->assertOk()
            ->assertJsonPath("message", "User deleted successfully");

        $this->assertDatabaseMissing("users", [
            "user_id" => $user->user_id,
        ]);
    }

    public function test_admin_and_satpam_can_manage_lostfound_flow(): void
    {
        $admin = User::factory()->admin()->create();
        $satpam = User::factory()->satpam()->create();

        $this->actingAs($satpam)
            ->postJson(route("admin.lostfound.store"), [
                "item_name" => "Dompet Testing",
                "description" => "Dompet ditemukan saat testing.",
                "location" => "Kantin",
                "status" => "found",
            ])
            ->assertCreated()
            ->assertJsonPath("message", "Barang berhasil diposting!");

        $item = LostfoundItem::where(
            "item_name",
            "Dompet Testing",
        )->firstOrFail();

        $this->actingAs($satpam)
            ->postJson(
                route("admin.lostfound.comments.store", $item->lostfound_id),
                [
                    "comment" => "Komentar dari satpam.",
                ],
            )
            ->assertCreated()
            ->assertJsonPath("message", "Komentar berhasil dikirim.");

        $comment = LostfoundComment::where(
            "lostfound_id",
            $item->lostfound_id,
        )->firstOrFail();

        $this->actingAs($admin)
            ->getJson(route("admin.lostfound.comments", $item->lostfound_id))
            ->assertOk()
            ->assertJsonStructure(["data"]);

        $this->actingAs($admin)
            ->postJson(route("admin.lostfound.resolve", $item->lostfound_id))
            ->assertOk()
            ->assertJsonPath("status", "claimed");

        $this->actingAs($admin)
            ->deleteJson(
                route("admin.lostfound.comments.destroy", $comment->comment_id),
            )
            ->assertForbidden();

        $this->actingAs($satpam)
            ->putJson(
                route("admin.lostfound.comments.update", $comment->comment_id),
                [
                    "comment" => "Komentar dari satpam sudah diedit.",
                ],
            )
            ->assertOk()
            ->assertJsonPath("message", "Komentar berhasil diperbarui.");

        $this->assertDatabaseHas("lostfound_comments", [
            "comment_id" => $comment->comment_id,
            "comment" => "Komentar dari satpam sudah diedit.",
        ]);

        $this->actingAs($satpam)
            ->deleteJson(
                route("admin.lostfound.comments.destroy", $comment->comment_id),
            )
            ->assertOk()
            ->assertJsonPath("message", "Komentar berhasil dihapus.");

        $this->actingAs($admin)
            ->deleteJson(route("admin.lostfound.destroy", $item->lostfound_id))
            ->assertOk()
            ->assertJsonPath("message", "Data barang berhasil dihapus!");

        $this->assertSoftDeleted("lostfound_items", [
            "lostfound_id" => $item->lostfound_id,
        ]);
    }

    public function test_admin_can_approve_reject_report_and_delete_teams(): void
    {
        $admin = User::factory()->admin()->create();
        $leader = User::factory()->regularUser()->create();
        $memberUser = User::factory()->regularUser()->create();

        $team = Team::factory()->create(["status" => "pending"]);
        TeamMember::factory()->create([
            "team_id" => $team->team_id,
            "user_id" => $leader->user_id,
            "role" => "leader",
            "status" => "pending",
        ]);

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->postJson(route("admin.teams.respond", $team->team_id), [
                "action" => "approve",
                "type" => "team",
            ])
            ->assertOk()
            ->assertJsonPath("data.status", "approved");

        $this->assertDatabaseHas("teams", [
            "team_id" => $team->team_id,
            "status" => "approved",
        ]);

        $member = TeamMember::factory()->create([
            "team_id" => $team->team_id,
            "user_id" => $memberUser->user_id,
            "role" => "member",
            "status" => "pending",
        ]);

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->postJson(route("admin.teams.respond", $member->member_id), [
                "action" => "reject",
                "type" => "member",
                "rejection_reason" => "Kuota sudah penuh.",
            ])
            ->assertOk()
            ->assertJsonPath("data.status", "rejected");

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->postJson(route("admin.teams.report", $team->team_id), [
                "achievement_rank" => "Juara 1",
                "competition_level" => "nasional",
            ])
            ->assertOk()
            ->assertJsonPath("achievement_rank", "Juara 1");

        $this->actingAs($admin)
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->deleteJson(route("admin.teams.destroy", $team->team_id))
            ->assertOk()
            ->assertJsonPath("message", "Tim berhasil dihapus.");

        $this->assertSoftDeleted("teams", [
            "team_id" => $team->team_id,
        ]);
    }
}
