<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Penjemputan extends Model
{
    use HasFactory;

    protected $table = 'penjemputan';

    public const DAFTAR_STATUS = ['menunggu', 'dijemput', 'dalam_perjalanan', 'tiba'];

    protected $fillable = [
        'kode_perjalanan',
        'jadwal_id',
        'anak_id',
        'pengemudi_id',
        'penjemput_id',
        'penerima_id',
        'tanggal',
        'status',
        'kode_verifikasi',
        'waktu_dijemput',
        'waktu_berangkat',
        'waktu_tiba',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_dijemput' => 'datetime',
        'waktu_berangkat' => 'datetime',
        'waktu_tiba' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'kode_perjalanan';
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pengemudi(): BelongsTo
    {
        return $this->belongsTo(Pengemudi::class);
    }

    public function penjemput(): BelongsTo
    {
        return $this->belongsTo(PihakBerwenang::class, 'penjemput_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(PihakBerwenang::class, 'penerima_id');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class);
    }

    public function serahTerima(): HasOne
    {
        return $this->hasOne(SerahTerima::class);
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

        if ($user->isPenerima()) {
            return $query->whereHas('penerima', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
