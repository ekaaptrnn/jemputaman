<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
use App\Models\PihakBerwenang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penjemputan>
 */
class PenjemputanFactory extends Factory
{
    protected $model = Penjemputan::class;

    public function definition(): array
    {
        $tanggal = fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
        $dateCode = str_replace('-', '', $tanggal);

        return [
            'kode_perjalanan' => 'JA-' . $dateCode . '-' . fake()->unique()->numerify('###'),
            'jadwal_id' => Jadwal::factory(),
            'anak_id' => Anak::factory(),
            'pengemudi_id' => Pengemudi::factory(),
            'penjemput_id' => PihakBerwenang::factory()->penjemput(),
            'penerima_id' => PihakBerwenang::factory()->penerima(),
            'tanggal' => $tanggal,
            'status' => 'menunggu',
            'kode_verifikasi' => fake()->numerify('######'),
            'waktu_dijemput' => null,
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'catatan' => null,
        ];
    }
}
