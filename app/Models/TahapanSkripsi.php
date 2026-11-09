<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahapanSkripsi extends Model
{
    protected $table = 'tahapan_skripsi';

    protected $fillable = [
        'skripsi_id',
        'nama_tahapan',
        'deskripsi',
        'urutan',
        'target_tanggal',
        'status',
        'tanggal_selesai',
    ];

    public function skripsi(): BelongsTo
    {
        return $this->belongsTo(Skripsi::class, 'skripsi_id');
    }

    public function progres(): HasMany
    {
        return $this->hasMany(Progres::class, 'tahapan_id');
    }
}