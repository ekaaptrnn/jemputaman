<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjemputan extends Model
{
    use HasFactory;

    protected $table = 'penjemputan';

    public const STATUS_DIJADWALKAN = 'dijadwalkan';
    public const STATUS_MENUJU_SEKOLAH = 'menuju_sekolah';
    public const STATUS_DISERAHKAN = 'diserahkan';
    public const STATUS_DALAM_PERJALANAN = 'dalam_perjalanan';
    public const STATUS_TIBA = 'tiba';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BERMASALAH = 'bermasalah';

    public const DAFTAR_STATUS = [
        self::STATUS_DIJADWALKAN,
        self::STATUS_MENUJU_SEKOLAH,
        self::STATUS_DISERAHKAN,
        self::STATUS_DALAM_PERJALANAN,
        self::STATUS_TIBA,
        self::STATUS_SELESAI,
        self::STATUS_BERMASALAH,
    ];

    protected $fillable = [
        'kode_perjalanan',
        'jadwal_id',
        'anak_id',
        'pengemudi_id',
        'tanggal',
        'status',
        'waktu_dijemput',
        'waktu_berangkat',
        'waktu_tiba',
        'foto_serah_sekolah',
        'foto_tiba',
        'waktu_konfirmasi_ortu',
        'dikonfirmasi_oleh',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_dijemput' => 'datetime',
        'waktu_berangkat' => 'datetime',
        'waktu_tiba' => 'datetime',
        'waktu_konfirmasi_ortu' => 'datetime',
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

    public function dikonfirmasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class);
    }

    public function lokasiDriver(): HasMany
    {
        return $this->hasMany(LokasiDriver::class);
    }

    /**
     * Scope akses per peran.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isGuru()) {
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
