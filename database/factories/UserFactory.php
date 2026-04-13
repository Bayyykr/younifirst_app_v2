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
        $statuses = ['active', 'inactive', 'suspended', 'blocked'];

        return [
            'user_id'    => strtoupper(substr($this->faker->unique()->bothify('USR#####'), 0, 10)),
            'name'       => $this->faker->name(),
            'email'      => $this->faker->unique()->safeEmail(),
            'password'   => static::$password ??= Hash::make('password'),
            'role'       => $this->faker->randomElement($roles),
            'nim'        => $this->faker->numerify('E41240###'),
            'prodi'      => $this->faker->randomElement($prodis),
            'photo'      => null,
            'status'     => $this->faker->randomElement($statuses),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
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
