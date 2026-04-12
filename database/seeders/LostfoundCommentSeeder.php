<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LostfoundCommentSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\LostfoundComment::factory()->count(25)->create();
    }
}
