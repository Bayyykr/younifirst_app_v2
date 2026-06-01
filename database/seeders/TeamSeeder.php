<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                "team_id" => "TM00000001",
                "team_name" => "Inovasi Digital Team",
                "competition_name" => "GEMASTIK",
                "description" =>
                    "Tim pengembangan solusi digital untuk kompetisi mahasiswa nasional.",
                "max_member" => 5,
                "status" => "approved",
            ],
            [
                "team_id" => "TM00000002",
                "team_name" => "Cyber Polije Team",
                "competition_name" => "Hackathon Nasional",
                "description" =>
                    "Tim yang berfokus pada keamanan aplikasi dan pengembangan prototipe cepat.",
                "max_member" => 4,
                "status" => "pending",
            ],
            [
                "team_id" => "TM00000003",
                "team_name" => "Data Science Team",
                "competition_name" => "PKM",
                "description" =>
                    "Tim riset dan implementasi machine learning untuk kebutuhan kampus.",
                "max_member" => 6,
                "status" => "approved",
            ],
            [
                "team_id" => "TM00000004",
                "team_name" => "Mobile Innovator Team",
                "competition_name" => "INAICTA",
                "description" =>
                    "Tim pengembangan aplikasi mobile berbasis kebutuhan mahasiswa.",
                "max_member" => 5,
                "status" => "approved",
            ],
            [
                "team_id" => "TM00000005",
                "team_name" => "Robotic Campus Team",
                "competition_name" => "Robotic Contest",
                "description" =>
                    "Tim robotika untuk eksplorasi otomasi dan Internet of Things.",
                "max_member" => 7,
                "status" => "pending",
            ],
        ];

        foreach ($teams as $team) {
            DB::table("teams")->updateOrInsert(
                ["team_id" => $team["team_id"]],
                $team + [
                    "rejection_reason" => null,
                    "created_at" => now(),
                    "updated_at" => null,
                    "deleted_at" => null,
                ],
            );
        }
    }
}
