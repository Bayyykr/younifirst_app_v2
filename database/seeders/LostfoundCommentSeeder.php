<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LostfoundCommentSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            [
                "comment_id" => "11111111-1111-1111-1111-111111111111",
                "lostfound_id" => "LF00000001",
                "user_id" => "USR41241",
                "comment" =>
                    "Saya melihat barang ini di meja dekat kasir kantin.",
            ],
            [
                "comment_id" => "22222222-2222-2222-2222-222222222222",
                "lostfound_id" => "LF00000002",
                "user_id" => "USR41240",
                "comment" =>
                    "Coba cek pos satpam dekat parkiran, biasanya kunci dititipkan di sana.",
            ],
            [
                "comment_id" => "33333333-3333-3333-3333-333333333333",
                "lostfound_id" => "LF00000003",
                "user_id" => "USR41241",
                "comment" => "Tumbler sudah diamankan oleh petugas kelas.",
            ],
        ];

        foreach ($comments as $comment) {
            DB::table("lostfound_comments")->updateOrInsert(
                ["comment_id" => $comment["comment_id"]],
                $comment + [
                    "created_at" => now(),
                    "update_at" => null,
                ],
            );
        }
    }
}
