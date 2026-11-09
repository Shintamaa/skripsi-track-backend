<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komentar extends Model
{
    protected $table = 'komentar';

    protected $fillable = [
        'progres_id',
        'pengirim_id',
        'isi_komentar',
    ];

    public function progres(): BelongsTo
    {
        return $this->belongsTo(Progres::class, 'progres_id');
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}