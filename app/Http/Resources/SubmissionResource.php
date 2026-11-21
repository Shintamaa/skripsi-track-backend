<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $file = $this->relationLoaded('lampiran') ? $this->lampiran->first() : null;

        return [
            'id' => $this->id,
            'stage_id' => $this->tahapan_id,
            'version' => $this->nomor_versi,
            'file_path' => $file?->path_file ?? '',
            'file_type' => $file?->mime_type ?? '',
            'file_url' => $file ? url('storage/' . $file->path_file) : null,
            'note' => $this->isi_progres,
            'status' => $this->status_review,
            'created_at' => ($this->submitted_at ?? $this->created_at)?->toIso8601String(),
            'comments' => CommentResource::collection($this->whenLoaded('komentar')),
        ];
    }
}