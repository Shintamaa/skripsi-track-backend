<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Aktivitas;
use App\Models\Komentar;
use App\Models\LampiranProgres;
use App\Models\Progres;
use App\Models\RiwayatRevisi;
use App\Models\TahapanSkripsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function store(Request $request, int $id)
    {
        $user = $request->user();
        $stage = TahapanSkripsi::with('skripsi')->findOrFail($id);

        abort_unless(
            $user->role === 'mahasiswa' && $stage->skripsi->mahasiswa_id === $user->id,
            403,
            'Hanya mahasiswa pemilik skripsi yang dapat mengunggah progres.'
        );

        $v = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $progres = DB::transaction(function () use ($request, $user, $stage, $v) {
            $terakhir = Progres::where('tahapan_id', $stage->id)
                ->orderByDesc('nomor_versi')
                ->first();
            $versi = ($terakhir?->nomor_versi ?? 0) + 1;

            $file = $request->file('file');
            $path = $file->store('lampiran', 'public');

            $progres = Progres::create([
                'tahapan_id' => $stage->id,
                'user_id' => $user->id,
                'isi_progres' => $v['note'] ?? null,
                'nomor_versi' => $versi,
                'progres_sebelumnya_id' => $terakhir?->id,
                'status_review' => 'menunggu',
                'submitted_at' => now(),
            ]);

            LampiranProgres::create([
                'progres_id' => $progres->id,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'mime_type' => $file->getClientMimeType(),
                'ukuran_file' => $file->getSize(),
            ]);

            if (in_array($stage->status, ['belum_dimulai', 'revisi'], true)) {
                $stage->update(['status' => 'proses', 'tanggal_selesai' => null]);
            }

            $this->catat($stage->skripsi_id, $user->id, 'upload',
                "Mengunggah progres versi {$versi} pada tahap {$stage->nama_tahapan}.");

            return $progres;
        });

        $progres->load(['lampiran', 'komentar.pengirim']);

        return response()->json([
            'success' => true,
            'message' => 'Progres berhasil diunggah.',
            'data' => new SubmissionResource($progres),
        ], 201);
    }

    public function updateStatus(Request $request, int $id)
    {
        $user = $request->user();
        $progres = Progres::with('tahapan.skripsi')->findOrFail($id);
        $stage = $progres->tahapan;

        abort_unless(
            $user->role === 'dosen' && $stage->skripsi->dosen_id === $user->id,
            403,
            'Hanya dosen pembimbing yang dapat mereview progres.'
        );

        $v = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'perlu_revisi'])],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($user, $progres, $stage, $v) {
            $progres->update(['status_review' => $v['status']]);

            if ($v['status'] === 'disetujui') {
                $stage->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => now()->toDateString(),
                ]);
            } else {
                $stage->update(['status' => 'revisi', 'tanggal_selesai' => null]);

                RiwayatRevisi::create([
                    'progres_id' => $progres->id,
                    'dosen_id' => $user->id,
                    'catatan_revisi' => $v['comment'] ?? '',
                ]);
            }

            if (! empty($v['comment'])) {
                Komentar::create([
                    'progres_id' => $progres->id,
                    'pengirim_id' => $user->id,
                    'isi_komentar' => $v['comment'],
                ]);
            }

            $aksi = $v['status'] === 'disetujui' ? 'menyetujui' : 'meminta revisi pada';
            $this->catat($stage->skripsi_id, $user->id, 'review',
                "Dosen {$aksi} progres versi {$progres->nomor_versi} tahap {$stage->nama_tahapan}.");
        });

        $progres->load(['lampiran', 'komentar.pengirim']);

        return response()->json([
            'success' => true,
            'message' => 'Status progres berhasil diperbarui.',
            'data' => new SubmissionResource($progres),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $progres = Progres::with(['tahapan.skripsi', 'lampiran'])->findOrFail($id);

        abort_unless($this->milikMahasiswa($progres, $user), 403, 'Kamu tidak berhak menghapus progres ini.');

        if ($progres->status_review === 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Progres yang sudah disetujui tidak dapat dihapus.',
            ], 422);
        }

        $this->hapus($progres);

        return response()->json([
            'success' => true,
            'message' => 'Progres berhasil dihapus.',
            'data' => null,
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $user = $request->user();

        $v = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);
        $ids = array_values(array_unique($v['ids']));

        $deleted = 0;
        foreach (Progres::with(['tahapan.skripsi', 'lampiran'])->whereIn('id', $ids)->get() as $progres) {
            if ($this->milikMahasiswa($progres, $user) && $progres->status_review !== 'disetujui') {
                $this->hapus($progres);
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses hapus massal selesai.',
            'data' => [
                'deleted' => $deleted,
                'skipped' => count($ids) - $deleted,
            ],
        ]);
    }

    private function milikMahasiswa(Progres $progres, $user): bool
    {
        return $user->role === 'mahasiswa'
            && $progres->tahapan->skripsi->mahasiswa_id === $user->id;
    }

    private function hapus(Progres $progres): void
    {
        DB::transaction(function () use ($progres) {
            foreach ($progres->lampiran as $lampiran) {
                Storage::disk('public')->delete($lampiran->path_file);
            }

            $progres->lampiran()->delete();
            $progres->komentar()->delete();
            $progres->riwayatRevisi()->delete();
            $progres->progresBerikutnya()->update([
                'progres_sebelumnya_id' => $progres->progres_sebelumnya_id,
            ]);
            $progres->delete();
        });
    }

    private function catat(int $skripsiId, int $userId, string $jenis, string $deskripsi): void
    {
        Aktivitas::create([
            'skripsi_id' => $skripsiId,
            'user_id' => $userId,
            'jenis_aktivitas' => $jenis,
            'deskripsi' => $deskripsi,
        ]);
    }
}