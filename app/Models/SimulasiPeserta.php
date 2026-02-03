<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulasiPeserta extends Model
{
    protected $table = 'simulasi_peserta';

    protected $fillable = [
        'simulasi_id',
        'user_id',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'nilai',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'nilai' => 'float',
    ];

    public function simulasi()
    {
        return $this->belongsTo(Simulasi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
