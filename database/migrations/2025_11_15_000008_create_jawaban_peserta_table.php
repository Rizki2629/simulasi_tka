<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jawaban_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulasi_peserta_id')->constrained('simulasi_peserta')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('soal')->onDelete('cascade');
            $table->foreignId('pilihan_jawaban_id')->nullable()->constrained('pilihan_jawaban')->onDelete('cascade');
            $table->text('jawaban_teks')->nullable(); // untuk isian dan uraian
            $table->json('jawaban_multiple')->nullable(); // untuk MCMA
            $table->boolean('is_benar')->nullable();
            $table->decimal('skor', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['simulasi_peserta_id', 'soal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_peserta');
    }
};
