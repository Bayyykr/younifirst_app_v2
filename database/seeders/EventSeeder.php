<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                "event_id" => "EVT0000001",
                "category_id" => 1,
                "title" => "Seminar Karier Teknologi",
                "description" =>
                    "Seminar persiapan karier di bidang teknologi informasi bersama praktisi industri.",
                "start_date" => now()->addDays(7),
                "end_date" => now()->addDays(7)->addHours(3),
                "location" => "Aula Polije",
                "status" => "upcoming",
            ],
            [
                "event_id" => "EVT0000002",
                "category_id" => 2,
                "title" => "Workshop Laravel API",
                "description" =>
                    "Workshop membangun REST API menggunakan Laravel dan autentikasi token.",
                "start_date" => now()->addDays(14),
                "end_date" => now()->addDays(14)->addHours(4),
                "location" => "Lab Software Engineering",
                "status" => "pending",
            ],
            [
                "event_id" => "EVT0000003",
                "category_id" => 3,
                "title" => "Hackathon Kampus",
                "description" =>
                    "Kompetisi pengembangan aplikasi untuk menyelesaikan masalah di lingkungan kampus.",
                "start_date" => now()->addDays(21),
                "end_date" => now()->addDays(22),
                "location" => "Gedung TI Polije",
                "status" => "upcoming",
            ],
            [
                "event_id" => "EVT0000004",
                "category_id" => 5,
                "title" => "Turnamen Futsal Mahasiswa",
                "description" =>
                    "Turnamen futsal antar program studi untuk mempererat kolaborasi mahasiswa.",
                "start_date" => now()->subDays(3),
                "end_date" => now()->addDays(1),
                "location" => "Lapangan Polije",
                "status" => "ongoing",
            ],
            [
                "event_id" => "EVT0000005",
                "category_id" => 8,
                "title" => "Aksi Sosial Bersama",
                "description" =>
                    "Kegiatan sosial mahasiswa untuk membantu masyarakat sekitar kampus.",
                "start_date" => now()->subDays(14),
                "end_date" => now()->subDays(14)->addHours(5),
                "location" => "Jember",
                "status" => "completed",
            ],
        ];

        foreach ($events as $event) {
            DB::table("events")->updateOrInsert(
                ["event_id" => $event["event_id"]],
                $event + [
                    "poster" => null,
                    "created_by" => "ADM0000001",
                    "rejection_reason" => null,
                    "created_at" => Carbon::now(),
                    "updated_at" => null,
                    "deleted_at" => null,
                ],
            );
        }
    }
}
