<?php

namespace Database\Factories;

use App\Models\Notifikasi;
use App\Models\Penjemputan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notifikasi>
 */
class NotifikasiFactory extends Factory
{
    protected $model = Notifikasi::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'penjemputan_id' => null,
            'judul' => fake()->randomElement([
                'Penjemputan Dimulai',
                'Anak Telah Dijemput',
                'Perjalanan Menuju Tujuan',
                'Anak Telah Tiba',
                'Serah Terima Selesai',
            ]),
            'isi' => 'Pemberitahuan: Penjemputan anak sedang dalam proses sesuai jadwal.',
            'dibaca_at' => null,
        ];
    }
}
