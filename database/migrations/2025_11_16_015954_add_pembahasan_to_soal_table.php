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
        Schema::table('soal', function (Blueprint $table) {
            $table->text('pembahasan')->nullable()->after('jawaban_benar');
            $table->string('gambar_pembahasan')->nullable()->after('pembahasan');
            $table->text('kunci_jawaban')->nullable()->after('gambar_pembahasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn(['pembahasan', 'gambar_pembahasan', 'kunci_jawaban']);
        });
    }
};
