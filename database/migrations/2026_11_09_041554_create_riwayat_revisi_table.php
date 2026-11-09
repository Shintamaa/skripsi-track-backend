<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_revisi', function (Blueprint $table) {

            $table->id();

            $table->foreignId('progres_id')
                  ->constrained('progres')
                  ->cascadeOnDelete();

            $table->foreignId('dosen_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->text('catatan_revisi');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_revisi');
    }
};