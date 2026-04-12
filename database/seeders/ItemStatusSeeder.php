<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['status_id' => 1, 'name_status' => 'Lost'],
            ['status_id' => 2, 'name_status' => 'Found'],
            ['status_id' => 3, 'name_status' => 'Returned'],
            ['status_id' => 4, 'name_status' => 'Claimed'],
        ];

        DB::table('item_status')->insert($statuses);
    }
}
