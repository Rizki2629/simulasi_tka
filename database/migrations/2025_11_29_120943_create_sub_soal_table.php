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
        Schema::create('sub_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')->constrained('soals')->onDelete('cascade');
            $table->integer('nomor_urut'); // Nomor pertanyaan (1, 2, 3, dst)
            $table->enum('jenis_soal', ['pilihan_ganda', 'benar_salah', 'mcma', 'isian', 'uraian']);
            $table->text('pertanyaan');
            $table->string('gambar_pertanyaan')->nullable();
            $table->text('jawaban_benar')->nullable();
            $table->text('kunci_jawaban')->nullable();
            $table->text('pembahasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_soal');
    }
};
