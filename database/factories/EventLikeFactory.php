<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventLike>
 */
class EventLikeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'like_id'    => (string) Str::uuid(),
            'event_id'   => Event::inRandomOrder()->value('event_id'),
            'user_id'    => User::inRandomOrder()->value('user_id'),
            'created_at' => now(),
        ];
    }
}
