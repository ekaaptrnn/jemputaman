<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anak extends Model
{
    use HasFactory;

    protected $table = 'anak';

    protected $fillable = [
        'orang_tua_id',
        'sekolah_id',
        'nama',
        'kelas',
        'kode_qr',
    ];

    public function orangTua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orang_tua_id');
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
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
        if ($user->isAdmin() || $user->isGuru()) {
            return $query;
        }

        if ($user->isOrangTua()) {
            return $query->where('orang_tua_id', $user->id);
        }

        if ($user->isPengemudi()) {
            return $query->whereHas('penjemputan', function (Builder $q) use ($user) {
                $q->whereHas('pengemudi', function (Builder $p) use ($user) {
                    $p->where('user_id', $user->id);
                });
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
