<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerahTerima extends Model
{
    use HasFactory;

    protected $table = 'serah_terima';

    protected $fillable = [
        'penjemputan_id',
        'penerima_id',
        'kode_input',
        'dikonfirmasi_at',
        'bukti_path',
    ];

    protected $casts = [
        'dikonfirmasi_at' => 'datetime',
    ];

    public function penjemputan(): BelongsTo
    {
        return $this->belongsTo(Penjemputan::class);
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(PihakBerwenang::class, 'penerima_id');
    }
}
