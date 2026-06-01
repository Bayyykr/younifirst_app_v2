<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                "announcement_id" => "ANN0000001",
                "title" => "Selamat Datang di Younifirst",
                "content" =>
                    "Younifirst hadir untuk membantu mahasiswa mengelola aktivitas kampus, event, tim, dan laporan lost and found.",
                "status" => "publish",
            ],
            [
                "announcement_id" => "ANN0000002",
                "title" => "Update Fitur Event Center",
                "content" =>
                    "Mahasiswa dapat melihat informasi event terbaru dan menyimpan event yang diminati.",
                "status" => "publish",
            ],
            [
                "announcement_id" => "ANN0000003",
                "title" => "Panduan Lost and Found",
                "content" =>
                    "Laporkan barang hilang atau barang ditemukan dengan detail lokasi dan deskripsi yang jelas.",
                "status" => "publish",
            ],
            [
                "announcement_id" => "ANN0000004",
                "title" => "Maintenance Sistem",
                "content" =>
                    "Akan dilakukan maintenance berkala untuk meningkatkan kualitas layanan aplikasi.",
                "status" => "draft",
            ],
        ];

        foreach ($announcements as $announcement) {
            DB::table("announcements")->updateOrInsert(
                ["announcement_id" => $announcement["announcement_id"]],
                $announcement + [
                    "file" => null,
                    "created_by" => "ADM0000001",
                    "created_at" => now(),
                    "deleted_at" => null,
                ],
            );
        }
    }
}
