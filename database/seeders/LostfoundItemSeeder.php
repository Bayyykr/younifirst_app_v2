<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LostfoundItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                "lostfound_id" => "LF00000001",
                "user_id" => "USR41240",
                "item_name" => "Dompet Hitam",
                "description" =>
                    "Dompet berwarna hitam ditemukan di area kantin.",
                "location" => "Kantin Polije",
                "status" => "found",
            ],
            [
                "lostfound_id" => "LF00000002",
                "user_id" => "USR41241",
                "item_name" => "Kunci Motor",
                "description" => "Kunci motor hilang dengan gantungan biru.",
                "location" => "Parkiran Gedung TI",
                "status" => "lost",
            ],
            [
                "lostfound_id" => "LF00000003",
                "user_id" => "USR41240",
                "item_name" => "Tumbler Stainless",
                "description" =>
                    "Tumbler tertinggal di ruang kelas setelah perkuliahan.",
                "location" => "Ruang Kelas TI 2",
                "status" => "found",
            ],
            [
                "lostfound_id" => "LF00000004",
                "user_id" => "USR41241",
                "item_name" => "Kartu Mahasiswa",
                "description" => "KTM hilang atas nama mahasiswa Polije.",
                "location" => "Perpustakaan",
                "status" => "lost",
            ],
        ];

        foreach ($items as $item) {
            DB::table("lostfound_items")->updateOrInsert(
                ["lostfound_id" => $item["lostfound_id"]],
                $item + [
                    "photo" => null,
                    "created_at" => now(),
                    "updated_at" => null,
                    "deleted_at" => null,
                ],
            );
        }
    }
}
