<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PihakBerwenang extends Model
{
    use HasFactory;

    protected $table = 'pihak_berwenang';

    protected $fillable = [
        'anak_id',
        'user_id',
        'nama',
        'hubungan',
        'tipe',
        'no_hp',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            return $query->where(function (Builder $q) use ($user) {
                $q->whereIn('id', function ($sub) use ($user) {
                    $sub->select('penjemput_id')
                        ->from('penjemputan')
                        ->whereIn('pengemudi_id', function ($subPeng) use ($user) {
                            $subPeng->select('id')->from('pengemudi')->where('user_id', $user->id);
                        });
                })->orWhereIn('id', function ($sub) use ($user) {
                    $sub->select('penerima_id')
                        ->from('penjemputan')
                        ->whereIn('pengemudi_id', function ($subPeng) use ($user) {
                            $subPeng->select('id')->from('pengemudi')->where('user_id', $user->id);
                        });
                });
            });
        }

        if ($user->isPenerima()) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }
}
