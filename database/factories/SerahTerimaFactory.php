<?php

namespace Database\Factories;

use App\Models\Penjemputan;
use App\Models\PihakBerwenang;
use App\Models\SerahTerima;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SerahTerima>
 */
class SerahTerimaFactory extends Factory
{
    protected $model = SerahTerima::class;

    public function definition(): array
    {
        return [
            'penjemputan_id' => Penjemputan::factory(),
            'penerima_id' => PihakBerwenang::factory()->penerima(),
            'kode_input' => fake()->numerify('######'),
            'dikonfirmasi_at' => now(),
            'bukti_path' => null,
        ];
    }
}
