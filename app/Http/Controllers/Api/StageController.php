<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StageResource;
use App\Models\TahapanSkripsi;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function show(Request $request, int $id)
    {
        $stage = TahapanSkripsi::with([
                'skripsi.mahasiswa',
                'skripsi.dosen',
                'progres' => fn ($q) => $q
                    ->with(['lampiran', 'komentar.pengirim'])
                    ->orderByDesc('nomor_versi'),
            ])
            ->withCount('progres as submissions_count')
            ->findOrFail($id);

        $user = $request->user();
        abort_unless(
            $stage->skripsi->mahasiswa_id === $user->id || $stage->skripsi->dosen_id === $user->id,
            403,
            'Kamu tidak memiliki akses ke tahapan ini.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Detail tahapan berhasil diambil.',
            'data' => new StageResource($stage),
        ]);
    }
}