<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemStatus>
 */
class ItemStatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            // status_id di-set manual di seeder
            'name_status' => $this->faker->randomElement([
                'Lost', 'Found', 'Returned', 'Claimed'
            ]),
        ];
    }
}
