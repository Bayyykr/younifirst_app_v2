<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        $competitions = [
            'GEMASTIK', 'INAICTA', 'Hackathon Nasional', 'PKM', 'ONMIPA',
            'Olimpiade Sains Mahasiswa', 'ACM ICPC', 'Robotic Contest',
        ];

        return [
            'team_id'          => strtoupper(substr($this->faker->unique()->bothify('TM######'), 0, 10)),
            'team_name'        => $this->faker->words(2, true) . ' Team',
            'competition_name' => $this->faker->randomElement($competitions),
            'description'      => $this->faker->paragraph(),
            'max_member'       => $this->faker->numberBetween(3, 10),
            'created_at'       => now(),
            'updated_at'       => null,
            'deleted_at'       => null,
        ];
    }
}
