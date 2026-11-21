<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\StageResource;
use App\Models\Aktivitas;
use App\Models\Progres;
use App\Models\Skripsi;
use App\Models\TahapanSkripsi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Nilai status di database. Cocokkan dengan migration kalau berbeda.
    private const TAHAPAN_SELESAI = 'selesai';
    private const REVIEW_MENUNGGU = 'menunggu';
    private const REVIEW_REVISI = 'revisi';

    public function index(Request $request)
    {
        $user = $request->user();

        // Dosen melihat semua bimbingannya, mahasiswa hanya skripsinya sendiri
        $skripsiIds = Skripsi::query()
            ->when(
                $user->role === 'dosen',
                fn ($q) => $q->where('dosen_id', $user->id),
                fn ($q) => $q->where('mahasiswa_id', $user->id)
            )
            ->pluck('id');

        $tahapan = TahapanSkripsi::whereIn('skripsi_id', $skripsiIds);
        $totalTahapan = (clone $tahapan)->count();
        $tahapanSelesai = (clone $tahapan)
            ->where('status', self::TAHAPAN_SELESAI)
            ->count();

        $progres = Progres::whereHas(
            'tahapan',
            fn ($q) => $q->whereIn('skripsi_id', $skripsiIds)
        );
        $menungguReview = (clone $progres)
            ->where('status_review', self::REVIEW_MENUNGGU)
            ->count();
        $perluRevisi = (clone $progres)
            ->where('status_review', self::REVIEW_REVISI)
            ->count();

        $deadline = (clone $tahapan)
            ->where('status', '!=', self::TAHAPAN_SELESAI)
            ->whereNotNull('target_tanggal')
            ->orderBy('target_tanggal')
            ->first();

        $aktivitas = Aktivitas::with('pengguna')
            ->whereIn('skripsi_id', $skripsiIds)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'total_skripsi' => $skripsiIds->count(),
                'progress_persen' => $totalTahapan > 0
                    ? (int) round($tahapanSelesai / $totalTahapan * 100)
                    : 0,
                'tahapan_selesai' => $tahapanSelesai,
                'total_tahapan' => $totalTahapan,
                'menunggu_review' => $menungguReview,
                'perlu_revisi' => $perluRevisi,
                'deadline_terdekat' => $deadline
                    ? new StageResource($deadline)
                    : null,
                'aktivitas_terbaru' => ActivityResource::collection($aktivitas),
            ],
        ]);
    }
}