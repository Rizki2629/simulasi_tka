<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulasi extends Model
{
    protected $table = 'simulasi';
    
    protected $fillable = [
        'nama_simulasi',
        'deskripsi',
        'mata_pelajaran_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function simulasiSoal()
    {
        return $this->hasMany(SimulasiSoal::class);
    }

    public function simulasiPeserta()
    {
        return $this->hasMany(SimulasiPeserta::class);
    }
}
