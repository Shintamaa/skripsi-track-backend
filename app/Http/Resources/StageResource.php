<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'thesis_id' => $this->skripsi_id,
            'name' => $this->nama_tahapan,
            'sort_order' => $this->urutan,
            'target_date' => $this->target_tanggal,
            'completed_at' => $this->tanggal_selesai,
            'status' => $this->status,
            'submissions_count' => $this->submissions_count ?? null,
            'submissions' => SubmissionResource::collection($this->whenLoaded('progres')),
            'thesis' => new ThesisResource($this->whenLoaded('skripsi')),
        ];
    }
}