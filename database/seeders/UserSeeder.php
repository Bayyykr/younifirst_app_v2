<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin utama dengan spesifik data
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@younifirst.com'],
            [
                'user_id'    => 'ADM0000001',
                'name'       => 'Admin Utama',
                'role'       => 'admin',
                'nim'        => null,
                'prodi'      => null,
                'password'   => Hash::make('password'),
            ]
        );

        // 10 Users dummy menggunakan factory
        \App\Models\User::factory()->count(10)->create();
    }
}
