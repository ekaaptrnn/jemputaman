<?php

namespace Database\Factories;

use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sekolah>
 */
class SekolahFactory extends Factory
{
    protected $model = Sekolah::class;

    public function definition(): array
    {
        return [
            'nama' => 'SD Negeri ' . fake('id_ID')->city() . ' ' . fake()->numberBetween(1, 10),
            'alamat' => 'Jl. ' . fake('id_ID')->streetName() . ' No. ' . fake()->numberBetween(1, 100),
        ];
    }
}
