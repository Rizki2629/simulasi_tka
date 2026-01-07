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
        Schema::create('sub_pilihan_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_soal_id')->constrained('sub_soal')->onDelete('cascade');
            $table->string('label', 10); // A, B, C, D, E atau P1, P2, P3
            $table->text('teks_jawaban');
            $table->string('gambar_jawaban')->nullable();
            $table->boolean('is_benar')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_pilihan_jawaban');
    }
};
