<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'nim',
    'nidn',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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

    protected $appends = ['nim_nidn'];

protected function nimNidn(): Attribute
{
    return Attribute::get(fn () => $this->nim ?? $this->nidn);
}

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi mahasiswa dengan skripsi.
     * Satu mahasiswa memiliki satu skripsi.
     */
    public function skripsiMahasiswa(): HasOne
    {
        return $this->hasOne(Skripsi::class, 'mahasiswa_id');
    }

    /**
     * Relasi dosen dengan skripsi.
     * Satu dosen dapat membimbing banyak mahasiswa.
     */
    public function skripsiBimbingan(): HasMany
    {
        return $this->hasMany(Skripsi::class, 'dosen_id');
    }

    /**
     * Riwayat progres yang dibuat oleh pengguna.
     */
    public function progresDibuat(): HasMany
    {
        return $this->hasMany(Progres::class, 'user_id');
    }

    /**
     * Komentar yang dikirim oleh pengguna.
     */
    public function komentarDikirim(): HasMany
    {
        return $this->hasMany(Komentar::class, 'pengirim_id');
    }

    /**
     * Riwayat revisi yang diberikan oleh dosen.
     */
    public function revisiDiberikan(): HasMany
    {
        return $this->hasMany(RiwayatRevisi::class, 'dosen_id');
    }

    /** 
     * Riwayat aktivitas pengguna.
     */
    public function aktivitas(): HasMany
    {
        return $this->hasMany(Aktivitas::class, 'user_id');
    }
}
