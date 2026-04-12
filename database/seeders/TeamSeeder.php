<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // Hubungkan ke factory untuk membuat 5 tim
        \App\Models\Team::factory()->count(5)->create();
    }
}
