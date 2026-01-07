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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nipd')->nullable()->after('rombongan_belajar');
            $table->string('jenis_kelamin', 20)->nullable()->after('nipd');
            $table->string('nik')->nullable()->after('jenis_kelamin');
            $table->string('agama')->nullable()->after('nik');
            $table->text('alamat')->nullable()->after('agama');
            $table->string('rt', 5)->nullable()->after('alamat');
            $table->string('rw', 5)->nullable()->after('rt');
            $table->string('dusun')->nullable()->after('rw');
            $table->string('kelurahan')->nullable()->after('dusun');
            $table->string('kecamatan')->nullable()->after('kelurahan');
            $table->string('kode_pos', 10)->nullable()->after('kecamatan');
            $table->string('no_hp')->nullable()->after('kode_pos');
            $table->string('nama_ayah')->nullable()->after('no_hp');
            $table->string('nama_ibu')->nullable()->after('nama_ayah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nipd', 'jenis_kelamin', 'nik', 'agama', 'alamat', 
                'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 
                'kode_pos', 'no_hp', 'nama_ayah', 'nama_ibu'
            ]);
        });
    }
};
