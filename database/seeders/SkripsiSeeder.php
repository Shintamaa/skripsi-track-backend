<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Skripsi;
use App\Models\TahapanSkripsi;
use App\Models\Progres;

class SkripsiSeeder extends Seeder
{
    public function run(): void
    {
        $mahasiswa = User::where(
            'email',
            'mahasiswa@example.com'
        )->firstOrFail();

        $dosen = User::where(
            'email',
            'dosen@example.com'
        )->firstOrFail();

        // Membuat data skripsi
        $skripsi = Skripsi::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            [
                'dosen_id' => $dosen->id,
                'judul' => 'Sistem Monitoring Progres Skripsi',
                'deskripsi' => 'Penelitian untuk pengembangan SkripsiTrack.',
                'tanggal_mulai' => now()->toDateString(),
                'target_selesai' => now()->addMonths(6)->toDateString(),
                'status' => 'berjalan',
            ]
        );

        // Daftar tahapan skripsi
        $tahapan = [
            'Pengajuan Judul',
            'Penyusunan Proposal',
            'Seminar Proposal',
            'Pengumpulan Data',
            'Analisis Data',
            'Penyusunan Laporan',
            'Sidang Skripsi',
        ];

        foreach ($tahapan as $index => $nama) {

            TahapanSkripsi::updateOrCreate(
                [
                    'skripsi_id' => $skripsi->id,
                    'urutan' => $index + 1,
                ],
                [
                    'nama_tahapan' => $nama,
                    'deskripsi' => 'Tahapan ' . $nama,
                    'target_tanggal' => null,
                    'status' => 'belum_dimulai',
                    'tanggal_selesai' => null,
                ]
            );
        }

        // Contoh pengumpulan progres pertama
        $tahapanPertama = TahapanSkripsi::where(
            'skripsi_id',
            $skripsi->id
        )->where('urutan', 1)->firstOrFail();

        Progres::firstOrCreate(
            [
                'tahapan_id' => $tahapanPertama->id,
                'nomor_versi' => 1,
            ],
            [
                'user_id' => $mahasiswa->id,
                'isi_progres' => 'Saya sudah menyelesaikan pengajuan judul.',
                'status_review' => 'menunggu',
                'submitted_at' => now(),
            ]
        );
    }
}