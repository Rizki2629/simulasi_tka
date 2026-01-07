<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable FK checks to allow table swapping
        Schema::disableForeignKeyConstraints();

        // 1. Create temp table with CORRECTED Foreign Key ('soal' instead of 'soals')
        Schema::create('sub_soal_temp', function (Blueprint $table) {
            $table->id();
            // Fix: Referencing 'soal' table, not 'soals'
            $table->foreignId('soal_id')->constrained('soal')->onDelete('cascade');
            
            $table->integer('nomor_urut');
            // Keeping enum as per original, or string for flexibility. Let's use string to match 'soal' table changes.
            $table->string('jenis_soal'); 
            
            $table->text('pertanyaan');
            $table->string('gambar_pertanyaan')->nullable();
            $table->text('jawaban_benar')->nullable();
            $table->text('kunci_jawaban')->nullable();
            $table->text('pembahasan')->nullable();
            $table->timestamps();
        });

        // 2. Copy data
        // Explicitly insert columns to ensure safety. 
        // Note: We cast enum to string implicitly during copy if we changed column type, which is fine.
        $columns = ['id', 'soal_id', 'nomor_urut', 'jenis_soal', 'pertanyaan', 'gambar_pertanyaan', 'jawaban_benar', 'kunci_jawaban', 'pembahasan', 'created_at', 'updated_at'];
        
        // Helper to quote identifiers for safety
        $quotedColumns = array_map(function($c) { return '"'.$c.'"'; }, $columns);
        $columnList = implode(', ', $quotedColumns);
        
        DB::statement("INSERT INTO sub_soal_temp ($columnList) SELECT $columnList FROM sub_soal");

        // 3. Drop old table
        Schema::drop('sub_soal');

        // 4. Rename temp table
        Schema::rename('sub_soal_temp', 'sub_soal');

        // Re-enable FK checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as this fixes a bug
    }
};
