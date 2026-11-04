<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
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
            'tanggal' => $tanggal,
            'status' => 'dijadwalkan',
            'waktu_dijemput' => null,
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'foto_serah_sekolah' => null,
            'foto_tiba' => null,
            'waktu_konfirmasi_ortu' => null,
            'dikonfirmasi_oleh' => null,
            'catatan' => null,
        ];
    }
}
