<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $firebase = app(\App\Services\FirebaseService::class);

        // 1. Admin utama
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@younifirst.com'],
            [
                'user_id'    => 'ADM0000001',
                'name'       => 'Admin Utama',
                'role'       => 'admin',
                'nim'        => null,
                'prodi'      => null,
                'password'   => Hash::make('password'),
                'status'     => 'active',
                'created_at' => now(),
            ]
        );

        $students = [
            [
                'user_id'  => 'USR41240',
                'name'     => 'Student 1',
                'email'    => 'e41240116@student.polije.ac.id',
                'password' => 'e41240116',
                'nim'      => 'E41240116',
                'prodi'    => 'Teknik Informatika',
            ],
            [
                'user_id'  => 'USR41241',
                'name'     => 'Student 2',
                'email'    => 'e41240259@student.polije.ac.id',
                'password' => 'e41240259',
                'nim'      => 'E41240259',
                'prodi'    => 'Teknik Informatika',
            ]
        ];

        foreach ($students as $data) {
            // Cek apakah user sudah ada di Firebase (untuk menghindari error Email Exists)
            // Seeder ini berasumsi jika gagal create karena email exists, kita tetap lanjut logikanya
            
            $uid = $firebase->createUser($data['email'], $data['password'], $data['name']);

            if (!$uid) {
                $this->command->warn("Gagal membuat user {$data['email']} di Firebase (Mungkin sudah ada).");
                // Kita coba ambil UID jika sudah ada (opsional, tapi FirebaseService belum ada getByEmail)
                // Untuk seeding, kita bisa biarkan firebase_uid kosong atau skip jika krusial.
            }

            \App\Models\User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'user_id'      => $data['user_id'],
                    'name'         => $data['name'],
                    'role'         => 'admin',
                    'nim'          => $data['nim'],
                    'prodi'        => $data['prodi'],
                    'password'     => Hash::make($data['password']),
                    'firebase_uid' => $uid,
                    'status'       => 'active',
                    'created_at'   => now(),
                ]
            );
            
            if ($uid) {
                $this->command->info("User {$data['email']} berhasil didaftarkan ke Firebase & DB.");
            }
        }
    }
}
