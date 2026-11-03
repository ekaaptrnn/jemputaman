<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\PihakBerwenang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PihakBerwenang>
 */
class PihakBerwenangFactory extends Factory
{
    protected $model = PihakBerwenang::class;

    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'user_id' => null,
            'nama' => fake('id_ID')->name(),
            'hubungan' => fake()->randomElement(['Kakek', 'Nenek', 'Paman', 'Bibi', 'Tetangga']),
            'tipe' => fake()->randomElement(['penjemput', 'penerima']),
            'no_hp' => '08' . fake()->numerify('##########'),
            'aktif' => true,
        ];
    }

    public function penjemput(): static
    {
        return $this->state(fn () => [
            'tipe' => 'penjemput',
            'user_id' => null,
        ]);
    }

    public function penerima(?int $userId = null): static
    {
        return $this->state(fn () => [
            'tipe' => 'penerima',
            'user_id' => $userId ?? User::factory()->penerima(),
        ]);
    }
}
