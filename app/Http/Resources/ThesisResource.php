<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThesisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->mahasiswa_id,
            'supervisor_id' => $this->dosen_id,
            'title' => $this->judul,
            'description' => $this->deskripsi,
            'status' => $this->status,
            'start_date' => $this->tanggal_mulai,
            'target_date' => $this->target_selesai,
            'student' => new UserMiniResource($this->whenLoaded('mahasiswa')),
            'supervisor' => new UserMiniResource($this->whenLoaded('dosen')),
            'stages_count' => $this->stages_count ?? null,
            'done_stages_count' => $this->done_stages_count ?? null,
            'stages' => StageResource::collection($this->whenLoaded('tahapan')),
        ];
    }
}