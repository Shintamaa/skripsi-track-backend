<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'submission_id' => $this->progres_id,
            'user_id' => $this->pengirim_id,
            'body' => $this->isi_komentar,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->relationLoaded('pengirim') && $this->pengirim
                ? [
                    'id' => $this->pengirim->id,
                    'name' => $this->pengirim->name,
                    'role' => $this->pengirim->role,
                ]
                : null,
        ];
    }
}