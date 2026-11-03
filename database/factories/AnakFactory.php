<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anak>
 */
class AnakFactory extends Factory
{
    protected $model = Anak::class;

    public function definition(): array
    {
        return [
            'orang_tua_id' => User::factory()->orangTua(),
            'sekolah_id' => Sekolah::factory(),
            'nama' => fake('id_ID')->name(),
            'kelas' => fake()->randomElement(['1A', '2B', '3C', '4A', '5B', '6C']),
        ];
    }
}
