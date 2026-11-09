<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatRevisi extends Model
{
    protected $table = 'riwayat_revisi';

    protected $fillable = [
        'progres_id',
        'dosen_id',
        'catatan_revisi',
    ];

    public function progres(): BelongsTo
    {
        return $this->belongsTo(Progres::class, 'progres_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}