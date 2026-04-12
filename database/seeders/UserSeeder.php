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
        \App\Models\User::factory()->create([
            'user_id'    => 'ADM0000001',
            'name'       => 'Admin Utama',
            'email'      => 'admin@younifirst.com',
            'role'       => 'admin',
            'nim'        => null,
            'prodi'      => null,
        ]);

        // 10 Users dummy menggunakan factory
        \App\Models\User::factory()->count(10)->create();
    }
}
