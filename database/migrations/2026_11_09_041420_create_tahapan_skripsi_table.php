<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahapan_skripsi', function (Blueprint $table) {

            $table->id();

            $table->foreignId('skripsi_id')
                  ->constrained('skripsi')
                  ->cascadeOnDelete();

            $table->string('nama_tahapan', 100);

            $table->text('deskripsi')->nullable();

            $table->unsignedSmallInteger('urutan');

            $table->date('target_tanggal')->nullable();

            $table->string('status', 30)->default('belum_dimulai');

            $table->date('tanggal_selesai')->nullable();

            $table->timestamps();

            $table->unique(['skripsi_id', 'urutan']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahapan_skripsi');
    }
};