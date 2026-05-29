<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnnouncementNotificationService
{
    public function __construct(private readonly FirebaseService $firebase) {}

    public function publishAndNotify(Announcement $announcement): int
    {
        if ($announcement->status !== "publish" || $announcement->notified_at) {
            return 0;
        }

        if (
            $announcement->publish_at &&
            $announcement->publish_at->isFuture()
        ) {
            return 0;
        }

        $lock = Cache::lock(
            "announcement-notification:{$announcement->announcement_id}",
            60,
        );

        if (!$lock->get()) {
            Log::info(
                "FCM: Announcement {$announcement->announcement_id} notification skipped because it is already being processed.",
            );
            return 0;
        }

        try {
            $announcement->refresh();

            if (
                $announcement->status !== "publish" ||
                $announcement->notified_at
            ) {
                return 0;
            }

            if (
                $announcement->publish_at &&
                $announcement->publish_at->isFuture()
            ) {
                return 0;
            }

            $tokens = User::query()
                ->where("fcm_token", "!=", "")
                ->select("fcm_token")
                ->distinct()
                ->pluck("fcm_token")
                ->filter()
                ->values();

            if ($tokens->isEmpty()) {
                Log::warning(
                    "FCM: Announcement {$announcement->announcement_id} notification skipped because no FCM tokens were found.",
                );
                return 0;
            }

            $sentCount = 0;

            foreach ($tokens as $token) {
                $sent = $this->firebase->sendNotification(
                    $token,
                    "Pengumuman Baru",
                    $announcement->title,
                    [
                        "announcement_id" =>
                            (string) $announcement->announcement_id,
                        "type" => "announcement_published",
                    ],
                );

                if ($sent) {
                    $sentCount++;
                }
            }

            if ($sentCount > 0) {
                $announcement->forceFill(["notified_at" => now()])->save();
            }

            Log::info(
                "FCM: Announcement {$announcement->announcement_id} notification sent to {$sentCount} unique devices from {$tokens->count()} tokens.",
            );

            return $sentCount;
        } finally {
            optional($lock)->release();
        }
    }
}
