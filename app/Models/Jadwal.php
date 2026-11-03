<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    protected $fillable = [
        'anak_id',
        'pengemudi_id',
        'sekolah_id',
        'hari',
        'jam_jemput',
        'titik_jemput',
        'titik_tujuan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pengemudi(): BelongsTo
    {
        return $this->belongsTo(Pengemudi::class);
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function penjemputan(): HasMany
    {
        return $this->hasMany(Penjemputan::class);
    }

    /**
     * Scope akses per peran.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isOrangTua()) {
            return $query->whereHas('anak', function (Builder $q) use ($user) {
                $q->where('orang_tua_id', $user->id);
            });
        }

        if ($user->isPengemudi()) {
            return $query->whereHas('pengemudi', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
