<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Urutan penting! Tabel yang jadi foreign key harus di-seed lebih dulu.
     */
    public function run(): void
    {
        $this->call([
            // 1. Tabel master / referensi (tidak punya FK)
            UserSeeder::class,
            ItemStatusSeeder::class,
            EventCategorySeeder::class,
            TeamSeeder::class,

            // 2. Tabel yang bergantung pada users & master
            AnnouncementSeeder::class,
            TeamMemberSeeder::class,
            EventSeeder::class,

            // 3. Tabel yang bergantung pada events & lostfound_items
            EventLikeSeeder::class,
            LostfoundItemSeeder::class,

            // 4. Tabel komentar (bergantung pada lostfound_items)
            LostfoundCommentSeeder::class,
        ]);
    }
}
