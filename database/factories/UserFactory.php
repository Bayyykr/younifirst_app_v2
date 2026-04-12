<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $roles = ['admin', 'user'];
        $prodis = [
            'Teknik Informatika',
            'Sistem Informasi',
            'Ilmu Komputer',
            'Teknik Elektro',
            'Manajemen',
            'Akuntansi',
        ];

        return [
            'user_id'    => strtoupper(substr($this->faker->unique()->bothify('USR#####'), 0, 10)),
            'name'       => $this->faker->name(),
            'email'      => $this->faker->unique()->safeEmail(),
            'password'   => static::$password ??= Hash::make('password'),
            'role'       => $this->faker->randomElement($roles),
            'nim'        => $this->faker->numerify('#########'),
            'prodi'      => $this->faker->randomElement($prodis),
            'photo'      => null,
            'status'     => 'active',
            'created_at' => now(),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a regular user.
     */
    public function regularUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
        ]);
    }
}
