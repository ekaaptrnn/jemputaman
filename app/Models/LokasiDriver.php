<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LokasiDriver extends Model
{
    use HasFactory;

    protected $table = 'lokasi_driver';

    protected $fillable = [
        'penjemputan_id',
        'latitude',
        'longitude',
        'dicatat_pada',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'dicatat_pada' => 'datetime',
    ];

    public function penjemputan(): BelongsTo
    {
        return $this->belongsTo(Penjemputan::class);
    }
}
