<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilai';
    
    protected $fillable = [
        'user_id',
        'simulasi_id',
        'mata_pelajaran_id',
        'nilai_total',
        'jumlah_benar',
        'jumlah_salah',
        'jumlah_soal',
        'detail_jawaban',
    ];

    protected $casts = [
        'nilai_total' => 'decimal:2',
        'detail_jawaban' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function simulasi()
    {
        return $this->belongsTo(Simulasi::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
}
