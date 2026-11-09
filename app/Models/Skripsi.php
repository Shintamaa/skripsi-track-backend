<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skripsi extends Model
{
    protected $table = 'skripsi';

    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'judul',
        'deskripsi',
        'tanggal_mulai',
        'target_selesai',
        'status',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function tahapan(): HasMany
    {
        return $this->hasMany(TahapanSkripsi::class, 'skripsi_id');
    }

    public function aktivitas(): HasMany
    {
        return $this->hasMany(Aktivitas::class, 'skripsi_id');
    }
}