<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aktivitas extends Model
{
    protected $table = 'aktivitas';

    protected $fillable = [
        'skripsi_id',
        'user_id',
        'jenis_aktivitas',
        'deskripsi',
    ];

    public function skripsi(): BelongsTo
    {
        return $this->belongsTo(Skripsi::class, 'skripsi_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}