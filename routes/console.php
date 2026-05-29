<?php

use App\Models\Announcement;
use App\Services\AnnouncementNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command("inspire", function () {
    $this->comment(Inspiring::quote());
})->purpose("Display an inspiring quote");

Artisan::command("announcements:publish-scheduled", function (
    AnnouncementNotificationService $notificationService,
) {
    $announcements = Announcement::query()
        ->where("status", "publish")
        ->whereNull("notified_at")
        ->where("publish_at", "<=", now())
        ->get();

    $published = 0;
    $notifications = 0;

    foreach ($announcements as $announcement) {
        $notifications += $notificationService->publishAndNotify($announcement);
        $published++;
    }

    $this->info(
        "Published {$published} scheduled announcements and sent {$notifications} notifications.",
    );
})->purpose("Publish scheduled announcements and send push notifications");

Schedule::command("announcements:publish-scheduled")->everyMinute();
