<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThesisResource;
use App\Models\Skripsi;
use App\Models\TahapanSkripsi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ThesisController extends Controller
{
    private const TAHAPAN_DEFAULT = [
        'Pengajuan Judul',
        'Penyusunan Proposal',
        'Seminar Proposal',
        'Pengumpulan Data',
        'Analisis Data',
        'Penyusunan Laporan',
        'Sidang Skripsi',
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $data = $this->baseQuery()
            ->when(
                $user->role === 'dosen',
                fn ($q) => $q->where('dosen_id', $user->id),
                fn ($q) => $q->where('mahasiswa_id', $user->id)
            )
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('judul', 'like', $term)
                      ->orWhereHas('mahasiswa', fn ($m) => $m->where('name', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->get();

        return $this->ok('Daftar skripsi berhasil diambil.', ThesisResource::collection($data));
    }

    public function show(Request $request, int $id)
    {
        $skripsi = $this->baseQuery()
            ->with(['tahapan' => fn ($q) => $q->orderBy('urutan')])
            ->findOrFail($id);

        $this->authorizeAccess($skripsi, $request->user());

        return $this->ok('Detail skripsi berhasil diambil.', new ThesisResource($skripsi));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        abort_unless($user->role === 'mahasiswa', 403, 'Hanya mahasiswa yang dapat membuat skripsi.');

        if (Skripsi::where('mahasiswa_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah memiliki skripsi.',
            ], 422);
        }

        $v = $request->validate([
            'supervisor_id' => ['required', Rule::exists('users', 'id')->where('role', 'dosen')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $skripsi = DB::transaction(function () use ($user, $v) {
            $skripsi = Skripsi::create([
                'mahasiswa_id' => $user->id,
                'dosen_id' => $v['supervisor_id'],
                'judul' => $v['title'],
                'deskripsi' => $v['description'] ?? null,
                'tanggal_mulai' => $v['start_date'] ?? now()->toDateString(),
                'target_selesai' => $v['target_date'] ?? null,
                'status' => 'berjalan',
            ]);

            foreach (self::TAHAPAN_DEFAULT as $i => $nama) {
                TahapanSkripsi::create([
                    'skripsi_id' => $skripsi->id,
                    'nama_tahapan' => $nama,
                    'deskripsi' => 'Tahapan ' . $nama,
                    'urutan' => $i + 1,
                    'status' => 'belum_dimulai',
                ]);
            }

            return $skripsi;
        });

        $skripsi = $this->baseQuery()
            ->with(['tahapan' => fn ($q) => $q->orderBy('urutan')])
            ->find($skripsi->id);

        return response()->json([
            'success' => true,
            'message' => 'Skripsi berhasil dibuat.',
            'data' => new ThesisResource($skripsi),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $skripsi = Skripsi::findOrFail($id);
        $this->authorizeAccess($skripsi, $request->user());

        $v = $request->validate([
            'supervisor_id' => ['sometimes', Rule::exists('users', 'id')->where('role', 'dosen')],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'target_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(['berjalan', 'selesai', 'ditunda'])],
        ]);

        $map = [
            'supervisor_id' => 'dosen_id',
            'title' => 'judul',
            'description' => 'deskripsi',
            'start_date' => 'tanggal_mulai',
            'target_date' => 'target_selesai',
            'status' => 'status',
        ];

        $data = [];
        foreach ($map as $input => $kolom) {
            if (array_key_exists($input, $v)) {
                $data[$kolom] = $v[$input];
            }
        }
        $skripsi->update($data);

        $skripsi = $this->baseQuery()
            ->with(['tahapan' => fn ($q) => $q->orderBy('urutan')])
            ->find($id);

        return $this->ok('Skripsi berhasil diperbarui.', new ThesisResource($skripsi));
    }

    public function destroy(Request $request, int $id)
    {
        $skripsi = Skripsi::findOrFail($id);
        $this->authorizeAccess($skripsi, $request->user());

        $skripsi->delete();

        return $this->ok('Skripsi berhasil dihapus.', null);
    }

    private function baseQuery()
    {
        return Skripsi::query()
            ->with(['mahasiswa', 'dosen'])
            ->withCount([
                'tahapan as stages_count',
                'tahapan as done_stages_count' => fn ($q) => $q->where('status', 'selesai'),
            ]);
    }

    private function authorizeAccess(Skripsi $skripsi, User $user): void
    {
        abort_unless(
            $skripsi->mahasiswa_id === $user->id || $skripsi->dosen_id === $user->id,
            403,
            'Kamu tidak memiliki akses ke skripsi ini.'
        );
    }

    private function ok(string $message, $data)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }
}