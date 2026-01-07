<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'soal';
    
    protected $fillable = [
        'kode_soal',
        'mata_pelajaran_id',
        'jenis_soal',
        'pertanyaan',
        'gambar_pertanyaan',
        'jawaban_benar',
        'pembahasan',
        'gambar_pembahasan',
        'kunci_jawaban',
        'bobot',
        'created_by',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function pilihanJawaban()
    {
        return $this->hasMany(PilihanJawaban::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function simulasiSoal()
    {
        return $this->hasMany(SimulasiSoal::class);
    }

    public function subSoal()
    {
        return $this->hasMany(SubSoal::class)->orderBy('nomor_urut');
    }
}
