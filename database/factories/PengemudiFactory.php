<?php

namespace Database\Factories;

use App\Models\Pengemudi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengemudi>
 */
class PengemudiFactory extends Factory
{
    protected $model = Pengemudi::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->pengemudi(),
            'no_kendaraan' => 'B ' . fake()->numerify('####') . ' ' . fake()->randomElement(['ABC', 'XYZ', 'JKT', 'BKS']),
            'jenis_kendaraan' => fake()->randomElement(['Mobil Avanza', 'Mobil Xenia', 'Mobil Innova', 'Motor Vario']),
            'aktif' => true,
        ];
    }
}
