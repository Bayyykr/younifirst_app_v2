<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'announcement_id' => strtoupper(substr($this->faker->unique()->bothify('ANN#####'), 0, 10)),
            'title'           => $this->faker->sentence(6),
            'content'         => $this->faker->paragraphs(3, true),
            'file'            => null,
            'created_by'      => User::inRandomOrder()->value('user_id'),
            'created_at'      => now(),
        ];
    }
}
