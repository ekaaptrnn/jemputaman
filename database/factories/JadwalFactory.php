<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Pengemudi;
use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jadwal>
 */
class JadwalFactory extends Factory
{
    protected $model = Jadwal::class;

    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'pengemudi_id' => Pengemudi::factory(),
            'sekolah_id' => Sekolah::factory(),
            'hari' => fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']),
            'jam_jemput' => fake()->randomElement(['12:00:00', '13:00:00', '14:30:00', '15:00:00']),
            'titik_jemput' => 'Gerbang SD Negeri ' . fake()->numberBetween(1, 10),
            'titik_tujuan' => 'Jl. Mawar No. ' . fake()->numberBetween(1, 50),
            'aktif' => true,
        ];
    }
}
