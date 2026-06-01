<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventLikeSeeder extends Seeder
{
    public function run(): void
    {
        $likes = [
            [
                "like_id" => "LIKE00000000000000000000000000000001",
                "event_id" => "EVT0000001",
                "user_id" => "USR41240",
            ],
            [
                "like_id" => "LIKE00000000000000000000000000000002",
                "event_id" => "EVT0000001",
                "user_id" => "USR41241",
            ],
            [
                "like_id" => "LIKE00000000000000000000000000000003",
                "event_id" => "EVT0000002",
                "user_id" => "USR41240",
            ],
            [
                "like_id" => "LIKE00000000000000000000000000000004",
                "event_id" => "EVT0000003",
                "user_id" => "USR41241",
            ],
        ];

        foreach ($likes as $like) {
            DB::table("event_likes")->updateOrInsert(
                ["like_id" => $like["like_id"]],
                $like + ["created_at" => now()],
            );
        }
    }
}
