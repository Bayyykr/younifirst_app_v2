<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LostfoundItemSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\LostfoundItem::factory()->count(15)->create();
    }
}
