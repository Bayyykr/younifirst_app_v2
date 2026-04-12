<?php

namespace Database\Factories;

use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+2 months');
        $endDate   = $this->faker->dateTimeBetween($startDate, '+3 months');

        return [
            'event_id'    => strtoupper(substr($this->faker->unique()->bothify('EVT#####'), 0, 10)),
            'category_id' => EventCategory::inRandomOrder()->value('category_id'),
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(2, true),
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'location'    => $this->faker->city() . ', ' . $this->faker->country(),
            'poster'      => null,
            'created_by'  => User::inRandomOrder()->value('user_id'),
            'status'      => $this->faker->randomElement(['upcoming', 'ongoing', 'completed', 'cancelled']),
            'created_at'  => now(),
            'updated_at'  => null,
            'deleted_at'  => null,
        ];
    }
}
