<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Aktivitas;
use App\Models\Komentar;
use App\Models\Progres;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, int $id)
    {
        $user = $request->user();
        $progres = Progres::with('tahapan.skripsi')->findOrFail($id);
        $skripsi = $progres->tahapan->skripsi;

        abort_unless(
            $skripsi->mahasiswa_id === $user->id || $skripsi->dosen_id === $user->id,
            403,
            'Kamu tidak memiliki akses ke progres ini.'
        );

        $v = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $komentar = Komentar::create([
            'progres_id' => $progres->id,
            'pengirim_id' => $user->id,
            'isi_komentar' => $v['body'],
        ]);

        Aktivitas::create([
            'skripsi_id' => $skripsi->id,
            'user_id' => $user->id,
            'jenis_aktivitas' => 'komentar',
            'deskripsi' => "Menambahkan komentar pada progres versi {$progres->nomor_versi}.",
        ]);

        $komentar->load('pengirim');

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil ditambahkan.',
            'data' => new CommentResource($komentar),
        ], 201);
    }
}