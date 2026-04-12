<?php

namespace Database\Factories;

use App\Models\LostfoundItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LostfoundComment>
 */
class LostfoundCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'comment_id'   => (string) Str::uuid(),
            'lostfound_id' => LostfoundItem::inRandomOrder()->value('lostfound_id'),
            'user_id'      => User::inRandomOrder()->value('user_id'),
            'comment'      => $this->faker->sentences(2, true),
            'created_at'   => now(),
            'update_at'    => null,
        ];
    }
}
