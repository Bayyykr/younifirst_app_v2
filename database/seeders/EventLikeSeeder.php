<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventLikeSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\EventLike::factory()->count(30)->create();
    }
}
