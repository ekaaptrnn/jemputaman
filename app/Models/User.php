<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'no_hp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke data anak (jika role orang_tua).
     */
    public function anak(): HasMany
    {
        return $this->hasMany(Anak::class, 'orang_tua_id');
    }

    /**
     * Relasi ke data pengemudi (jika role pengemudi).
     */
    public function pengemudi(): HasOne
    {
        return $this->hasOne(Pengemudi::class);
    }

    /**
     * Relasi ke notifikasi.
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    /**
     * Relasi ke riwayat konfirmasi penjemputan oleh orang tua.
     */
    public function konfirmasiPenjemputan(): HasMany
    {
        return $this->hasMany(Penjemputan::class, 'dikonfirmasi_oleh');
    }

    /**
     * Helper pengecekan peran.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isOrangTua(): bool
    {
        return $this->role === 'orang_tua';
    }

    public function isPengemudi(): bool
    {
        return $this->role === 'pengemudi';
    }
}
