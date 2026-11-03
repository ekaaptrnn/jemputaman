<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatus extends Model
{
    use HasFactory;

    protected $table = 'riwayat_status';

    protected $fillable = [
        'penjemputan_id',
        'diubah_oleh',
        'status',
        'waktu',
        'catatan',
    ];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    public function penjemputan(): BelongsTo
    {
        return $this->belongsTo(Penjemputan::class);
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
