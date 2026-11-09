<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Progres extends Model
{
    protected $table = 'progres';

    protected $fillable = [
        'tahapan_id',
        'user_id',
        'isi_progres',
        'nomor_versi',
        'progres_sebelumnya_id',
        'status_review',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(TahapanSkripsi::class, 'tahapan_id');
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function progresSebelumnya(): BelongsTo
    {
        return $this->belongsTo(
            Progres::class,
            'progres_sebelumnya_id'
        );
    }

    public function progresBerikutnya(): HasMany
    {
        return $this->hasMany(
            Progres::class,
            'progres_sebelumnya_id'
        );
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(LampiranProgres::class, 'progres_id');
    }

    public function komentar(): HasMany
    {
        return $this->hasMany(Komentar::class, 'progres_id');
    }

    public function riwayatRevisi(): HasMany
    {
        return $this->hasMany(RiwayatRevisi::class, 'progres_id');
    }
}