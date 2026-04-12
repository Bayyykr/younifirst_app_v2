<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMember>
 */
class TeamMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => strtoupper(substr($this->faker->unique()->bothify('MBR#####'), 0, 10)),
            'team_id'   => Team::inRandomOrder()->value('team_id'),
            'user_id'   => User::inRandomOrder()->value('user_id'),
            'role'      => $this->faker->randomElement(['leader', 'member']),
            'status'    => 'active',
        ];
    }
}
