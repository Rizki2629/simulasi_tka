<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get all soal with images
$soalWithImages = App\Models\Soal::whereNotNull('gambar_pertanyaan')
    ->orWhereHas('pilihanJawaban', function($q) {
        $q->whereNotNull('gambar_jawaban');
    })
    ->with('pilihanJawaban')
    ->get();

if ($soalWithImages->count() > 0) {
    echo "=== SOAL WITH IMAGES ===" . PHP_EOL;
    foreach ($soalWithImages as $soal) {
        echo PHP_EOL;
        echo "ID: " . $soal->id . " | Jenis: " . $soal->jenis_soal . PHP_EOL;
        echo "Pertanyaan: " . substr($soal->pertanyaan, 0, 50) . "..." . PHP_EOL;
        echo "Gambar Pertanyaan: " . ($soal->gambar_pertanyaan ?? 'NULL') . PHP_EOL;
        
        if ($soal->pilihanJawaban->count() > 0) {
            foreach ($soal->pilihanJawaban as $pilihan) {
                if ($pilihan->gambar_jawaban) {
                    echo "  " . $pilihan->label . ": " . $pilihan->gambar_jawaban . PHP_EOL;
                }
            }
        }
    }
} else {
    echo "NO SOAL WITH IMAGES FOUND!" . PHP_EOL;
    echo PHP_EOL;
    echo "Total soal in database: " . App\Models\Soal::count() . PHP_EOL;
}
