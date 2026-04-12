<?php

namespace Database\Factories;

use App\Models\ItemStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LostfoundItem>
 */
class LostfoundItemFactory extends Factory
{
    public function definition(): array
    {
        $items = [
            'Dompet', 'Kunci Motor', 'Tas Ransel', 'Laptop', 'HP Samsung',
            'iPhone', 'Buku Catatan', 'Kartu Mahasiswa', 'ATM BNI', 'Kacamata',
            'Helm', 'Jaket Hitam', 'Powerbank', 'Airpods', 'Charger Laptop',
        ];

        $locations = [
            'Gedung A Lantai 2', 'Perpustakaan', 'Kantin Utama',
            'Parkiran Motor', 'Aula Serbaguna', 'Lab Komputer 3',
            'Masjid Kampus', 'Lapangan Basket', 'Ruang BEM',
        ];

        return [
            'lostfound_id' => strtoupper(substr($this->faker->unique()->bothify('LF######'), 0, 10)),
            'user_id'      => User::inRandomOrder()->value('user_id'),
            'item_name'    => $this->faker->randomElement($items),
            'description'  => $this->faker->paragraph(),
            'photo'        => null,
            'location'     => $this->faker->randomElement($locations),
            'status_id'    => ItemStatus::inRandomOrder()->value('status_id'),
            'created_at'   => now(),
            'updated_at'   => null,
            'deleted_at'   => null,
        ];
    }
}
