<?php

namespace Database\Factories;

use App\Models\LokasiDriver;
use App\Models\Penjemputan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LokasiDriver>
 */
class LokasiDriverFactory extends Factory
{
    protected $model = LokasiDriver::class;

    public function definition(): array
    {
        return [
            'penjemputan_id' => Penjemputan::factory(),
            'latitude' => fake()->latitude(-7.58, -7.54), // Area Surakarta & sekitarnya
            'longitude' => fake()->longitude(110.80, 110.86),
            'dicatat_pada' => now(),
        ];
    }
}
