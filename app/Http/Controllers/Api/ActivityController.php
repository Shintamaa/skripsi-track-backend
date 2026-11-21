<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Aktivitas;
use App\Models\Skripsi;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $skripsiIds = Skripsi::query()
            ->when(
                $user->role === 'dosen',
                fn ($q) => $q->where('dosen_id', $user->id),
                fn ($q) => $q->where('mahasiswa_id', $user->id)
            )
            ->pluck('id');

        $page = Aktivitas::with('pengguna')
            ->whereIn('skripsi_id', $skripsiIds)
            ->when($request->filled('action'), fn ($q) => $q->where('jenis_aktivitas', $request->action))
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat aktivitas berhasil diambil.',
            'data' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
                'data' => ActivityResource::collection($page->getCollection()),
            ],
        ]);
    }
}