<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Team;
use App\Models\LostfoundItem;
use App\Mail\EventCreatedMail;
use App\Mail\TeamCreatedMail;
use App\Mail\LostfoundCreatedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_notification_settings(): void
    {
        $admin = User::factory()->admin()->create([
            'notify_email' => false,
            'notify_event' => false,
            'notify_team' => false,
            'notify_lostfound' => false,
        ]);

        $response = $this->actingAs($admin)->postJson(route('profile.update-notifications'), [
            'notify_email' => true,
            'notify_event' => true,
            'notify_team' => true,
            'notify_lostfound' => false,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $admin->refresh();
        $this->assertTrue($admin->notify_email);
        $this->assertTrue($admin->notify_event);
        $this->assertTrue($admin->notify_team);
        $this->assertFalse($admin->notify_lostfound);
    }

    public function test_event_created_sends_email_to_subscribed_admins(): void
    {
        Mail::fake();

        // Admin who wants notifications
        $admin1 = User::factory()->admin()->create([
            'email' => 'admin1@younifirst.com',
            'notify_email' => true,
            'notify_event' => true,
        ]);

        // Admin who doesn't want emails
        $admin2 = User::factory()->admin()->create([
            'email' => 'admin2@younifirst.com',
            'notify_email' => false,
            'notify_event' => true,
        ]);

        // Admin who wants emails but not for events
        $admin3 = User::factory()->admin()->create([
            'email' => 'admin3@younifirst.com',
            'notify_email' => true,
            'notify_event' => false,
        ]);

        $category = EventCategory::factory()->create(['category_id' => 1]);

        $event = Event::create([
            'event_id' => 'EV001',
            'category_id' => $category->category_id,
            'title' => 'Lomba Hackathon',
            'description' => 'Hackathon description',
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(3),
            'location' => 'Kampus A',
            'created_by' => $admin1->user_id,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        Mail::assertSent(EventCreatedMail::class, function ($mail) use ($admin1) {
            return $mail->hasTo($admin1->email);
        });

        Mail::assertNotSent(EventCreatedMail::class, function ($mail) use ($admin2) {
            return $mail->hasTo($admin2->email);
        });

        Mail::assertNotSent(EventCreatedMail::class, function ($mail) use ($admin3) {
            return $mail->hasTo($admin3->email);
        });
    }

    public function test_team_created_sends_email_to_subscribed_admins(): void
    {
        Mail::fake();

        $admin1 = User::factory()->admin()->create([
            'email' => 'admin1@younifirst.com',
            'notify_email' => true,
            'notify_team' => true,
        ]);

        $admin2 = User::factory()->admin()->create([
            'email' => 'admin2@younifirst.com',
            'notify_email' => true,
            'notify_team' => false,
        ]);

        $team = Team::factory()->create();

        Mail::assertSent(TeamCreatedMail::class, function ($mail) use ($admin1) {
            return $mail->hasTo($admin1->email);
        });

        Mail::assertNotSent(TeamCreatedMail::class, function ($mail) use ($admin2) {
            return $mail->hasTo($admin2->email);
        });
    }

    public function test_lostfound_created_sends_email_to_subscribed_admins(): void
    {
        Mail::fake();

        $admin1 = User::factory()->admin()->create([
            'email' => 'admin1@younifirst.com',
            'notify_email' => true,
            'notify_lostfound' => true,
        ]);

        $admin2 = User::factory()->admin()->create([
            'email' => 'admin2@younifirst.com',
            'notify_email' => false,
            'notify_lostfound' => true,
        ]);

        $user = User::factory()->create();
        $item = LostfoundItem::create([
            'lostfound_id' => 'LF001',
            'user_id' => $user->user_id,
            'item_name' => 'Kunci Motor',
            'description' => 'Kunci motor Honda Vario',
            'location' => 'Parkiran Gedung B',
            'status' => 'lost',
            'created_at' => now(),
        ]);

        Mail::assertSent(LostfoundCreatedMail::class, function ($mail) use ($admin1) {
            return $mail->hasTo($admin1->email);
        });

        Mail::assertNotSent(LostfoundCreatedMail::class, function ($mail) use ($admin2) {
            return $mail->hasTo($admin2->email);
        });
    }
}
