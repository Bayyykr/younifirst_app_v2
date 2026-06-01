<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                "member_id" => "MBR0000001",
                "team_id" => "TM00000001",
                "user_id" => "USR41240",
                "role" => "leader",
                "status" => "active",
            ],
            [
                "member_id" => "MBR0000002",
                "team_id" => "TM00000001",
                "user_id" => "USR41241",
                "role" => "member",
                "status" => "active",
            ],
            [
                "member_id" => "MBR0000003",
                "team_id" => "TM00000002",
                "user_id" => "USR41240",
                "role" => "leader",
                "status" => "pending",
            ],
            [
                "member_id" => "MBR0000004",
                "team_id" => "TM00000003",
                "user_id" => "USR41241",
                "role" => "leader",
                "status" => "active",
            ],
            [
                "member_id" => "MBR0000005",
                "team_id" => "TM00000004",
                "user_id" => "USR41240",
                "role" => "leader",
                "status" => "active",
            ],
        ];

        foreach ($members as $member) {
            DB::table("team_members")->updateOrInsert(
                ["member_id" => $member["member_id"]],
                $member + [
                    "rejection_reason" => null,
                    "portfolio" => null,
                    "proposed_role" => null,
                    "description" => null,
                ],
            );
        }
    }
}
