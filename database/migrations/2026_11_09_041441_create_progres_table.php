<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progres', function (Blueprint $table) {

            $table->id();

            $table->foreignId('tahapan_id')
                  ->constrained('tahapan_skripsi')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->text('isi_progres');

            $table->unsignedInteger('nomor_versi')->default(1);

            $table->foreignId('progres_sebelumnya_id')
                  ->nullable()
                  ->constrained('progres')
                  ->nullOnDelete();

            $table->string('status_review', 30)->default('menunggu');

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->unique(['tahapan_id', 'nomor_versi']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres');
    }
};