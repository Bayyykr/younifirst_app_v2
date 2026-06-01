<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $firebase = app(FirebaseService::class);

        $users = [
            [
                "user_id" => "ADM0000001",
                "name" => "Admin Utama",
                "email" => "admin@younifirst.com",
                "password" => "password",
                "role" => "admin",
                "nim" => null,
                "prodi" => null,
                "sync_firebase" => false,
            ],
            [
                "user_id" => "STP0000001",
                "name" => "Satpam Polije",
                "email" => "satpam@polije.ac.id",
                "password" => "satpam123",
                "role" => "satpam",
                "nim" => null,
                "prodi" => null,
                "sync_firebase" => true,
            ],
            [
                "user_id" => "USR41240",
                "name" => "Student 1",
                "email" => "e41240116@student.polije.ac.id",
                "password" => "e41240116",
                "role" => "user",
                "nim" => "E41240116",
                "prodi" => "Teknik Informatika",
                "sync_firebase" => true,
            ],
            [
                "user_id" => "USR41241",
                "name" => "Student 2",
                "email" => "e41240259@student.polije.ac.id",
                "password" => "e41240259",
                "role" => "user",
                "nim" => "E41240259",
                "prodi" => "Teknik Informatika",
                "sync_firebase" => true,
            ],
        ];

        foreach ($users as $data) {
            $firebaseUid = null;

            if ($data["sync_firebase"]) {
                $firebaseUid = $firebase->createUser(
                    $data["email"],
                    $data["password"],
                    $data["name"],
                );

                if (!$firebaseUid) {
                    $this->command?->warn(
                        "Firebase user {$data["email"]} tidak dibuat. Kemungkinan sudah ada atau Firebase belum tersedia.",
                    );
                }
            }

            $values = [
                "name" => $data["name"],
                "email" => $data["email"],
                "role" => $data["role"],
                "nim" => $data["nim"],
                "prodi" => $data["prodi"],
                "password" => Hash::make($data["password"]),
                "status" => "active",
                "created_at" => now(),
            ];

            if ($firebaseUid) {
                $values["firebase_uid"] = $firebaseUid;
            }

            User::updateOrCreate(["user_id" => $data["user_id"]], $values);

            $this->command?->info(
                "User {$data["email"]} ({$data["role"]}) berhasil disiapkan.",
            );
        }
    }
}
