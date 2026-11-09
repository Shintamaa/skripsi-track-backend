<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skripsi', function (Blueprint $table) {

            $table->id();

            $table->foreignId('mahasiswa_id')
                  ->unique()
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('dosen_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->string('judul');

            $table->text('deskripsi')->nullable();

            $table->date('tanggal_mulai')->nullable();

            $table->date('target_selesai')->nullable();

            $table->string('status', 30)->default('berjalan');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skripsi');
    }
};