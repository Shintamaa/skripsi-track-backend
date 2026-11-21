<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->jenis_aktivitas,
            'description' => $this->deskripsi,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->pengguna
                ? ['id' => $this->pengguna->id, 'name' => $this->pengguna->name]
                : null,
        ];
    }
}