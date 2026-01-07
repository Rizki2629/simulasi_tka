<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubPilihanJawaban extends Model
{
    use HasFactory;

    protected $table = 'sub_pilihan_jawaban';

    protected $fillable = [
        'sub_soal_id',
        'label',
        'teks_jawaban',
        'gambar_jawaban',
        'is_benar',
    ];

    protected $casts = [
        'is_benar' => 'boolean',
    ];

    public function subSoal()
    {
        return $this->belongsTo(SubSoal::class);
    }
}
