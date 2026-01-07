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
        // Disable foreign key constraints to allow table swapping
        Schema::disableForeignKeyConstraints();

        // Step 1: Create a temporary table with the new schema (jenis_soal as string)
        Schema::create('soal_temp', function (Blueprint $table) {
            $table->id();
            $table->string('kode_soal')->unique();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            
            // Changed from enum to string to allow 'paket' and any other types
            $table->string('jenis_soal'); 
            
            $table->text('pertanyaan');
            $table->string('gambar_pertanyaan')->nullable();
            $table->text('jawaban_benar')->nullable();
            
            // Columns added by subsequent migrations
            $table->text('pembahasan')->nullable();
            $table->string('gambar_pembahasan')->nullable();
            $table->text('kunci_jawaban')->nullable();
            
            $table->integer('bobot')->default(1);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Step 2: Copy data from the old table to the new table
        // We get the columns dyamically to ensure we match what is currently in DB
        $columns = Schema::getColumnListing('soal');
        $columnString = implode(', ', array_map(function($col) { return '"' . $col . '"'; }, $columns));
        
        if (!empty($columns)) {
            DB::statement("INSERT INTO soal_temp ($columnString) SELECT $columnString FROM soal");
        }

        // Step 3: Drop the old table and rename the new one
        Schema::drop('soal');
        Schema::rename('soal_temp', 'soal');

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not revert this as it may cause data loss if 'paket' type exists
    }
};
