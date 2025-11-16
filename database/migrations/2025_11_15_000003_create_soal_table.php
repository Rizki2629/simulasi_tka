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
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->string('kode_soal')->unique();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->enum('jenis_soal', ['pilihan_ganda', 'benar_salah', 'mcma', 'isian', 'uraian']);
            $table->text('pertanyaan');
            $table->string('gambar_pertanyaan')->nullable();
            $table->text('jawaban_benar')->nullable(); // untuk isian dan uraian
            $table->integer('bobot')->default(1);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
