<?php

namespace Database\Factories;

use App\Models\Penjemputan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RiwayatStatus>
 */
class RiwayatStatusFactory extends Factory
{
    protected $model = RiwayatStatus::class;

    public function definition(): array
    {
        return [
            'penjemputan_id' => Penjemputan::factory(),
            'diubah_oleh' => User::factory()->pengemudi(),
            'status' => 'menunggu',
            'waktu' => now(),
            'catatan' => 'Perubahan status tercatat secara otomatis.',
        ];
    }
}
