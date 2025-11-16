<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulasiSoal extends Model
{
    protected $table = 'simulasi_soal';
    
    protected $fillable = [
        'simulasi_id',
        'soal_id',
        'urutan',
    ];

    public function simulasi()
    {
        return $this->belongsTo(Simulasi::class);
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }
}
