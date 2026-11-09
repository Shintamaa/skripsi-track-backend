<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LampiranProgres extends Model
{
    protected $table = 'lampiran_progres';

    protected $fillable = [
        'progres_id',
        'nama_file',
        'path_file',
        'mime_type',
        'ukuran_file',
    ];

    public function progres(): BelongsTo
    {
        return $this->belongsTo(Progres::class, 'progres_id');
    }
}