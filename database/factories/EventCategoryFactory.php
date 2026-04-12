<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventCategory>
 */
class EventCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            // category_id di-set manual di seeder
            'name_category' => $this->faker->randomElement([
                'Seminar', 'Workshop', 'Kompetisi', 'Festival',
                'Olahraga', 'Seni & Budaya', 'Akademik', 'Sosial',
            ]),
        ];
    }
}
