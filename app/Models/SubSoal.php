<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSoal extends Model
{
    use HasFactory;

    protected $table = 'sub_soal';

    protected $fillable = [
        'soal_id',
        'nomor_urut',
        'jenis_soal',
        'pertanyaan',
        'gambar_pertanyaan',
        'jawaban_benar',
        'kunci_jawaban',
        'pembahasan',
    ];

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }

    public function pilihanJawaban()
    {
        return $this->hasMany(SubPilihanJawaban::class);
    }
}
