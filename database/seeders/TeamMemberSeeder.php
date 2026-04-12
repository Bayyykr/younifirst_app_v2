<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        // Karena TeamMember butuh team_id dan user_id yang valid, 
        // factory sudah menangani pengambilan ID secara random.
        \App\Models\TeamMember::factory()->count(20)->create();
    }
}
