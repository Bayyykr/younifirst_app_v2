<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_id' => 1, 'name_category' => 'Seminar'],
            ['category_id' => 2, 'name_category' => 'Workshop'],
            ['category_id' => 3, 'name_category' => 'Kompetisi'],
            ['category_id' => 4, 'name_category' => 'Festival'],
            ['category_id' => 5, 'name_category' => 'Olahraga'],
            ['category_id' => 6, 'name_category' => 'Seni & Budaya'],
            ['category_id' => 7, 'name_category' => 'Akademik'],
            ['category_id' => 8, 'name_category' => 'Sosial'],
        ];

        DB::table('event_categories')->insert($categories);
    }
}
