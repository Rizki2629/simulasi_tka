<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get latest soal
$soal = App\Models\Soal::with('pilihanJawaban')->latest()->first();

if ($soal) {
    echo "=== LATEST SOAL INFO ===" . PHP_EOL;
    echo "ID: " . $soal->id . PHP_EOL;
    echo "Kode: " . $soal->kode_soal . PHP_EOL;
    echo "Pertanyaan: " . substr($soal->pertanyaan, 0, 100) . "..." . PHP_EOL;
    echo "Gambar Pertanyaan: " . ($soal->gambar_pertanyaan ?? 'NULL') . PHP_EOL;
    echo "Jenis Soal: " . $soal->jenis_soal . PHP_EOL;
    echo "Updated At: " . $soal->updated_at . PHP_EOL;
    echo PHP_EOL;
    
    // Check if image file exists
    if ($soal->gambar_pertanyaan) {
        $fullPath = storage_path('app/public/' . $soal->gambar_pertanyaan);
        echo "Full Path: " . $fullPath . PHP_EOL;
        echo "File Exists: " . (file_exists($fullPath) ? 'YES ✓' : 'NO ✗') . PHP_EOL;
        if (file_exists($fullPath)) {
            echo "File Size: " . number_format(filesize($fullPath) / 1024, 2) . " KB" . PHP_EOL;
        }
    }
    
    echo PHP_EOL;
    echo "=== PILIHAN JAWABAN ===" . PHP_EOL;
    if ($soal->pilihanJawaban->count() > 0) {
        foreach ($soal->pilihanJawaban as $pilihan) {
            echo "Label: " . $pilihan->label . " | Teks: " . substr($pilihan->teks_jawaban ?? '', 0, 50) . PHP_EOL;
            echo "  Gambar: " . ($pilihan->gambar_jawaban ?? 'NULL') . PHP_EOL;
            if ($pilihan->gambar_jawaban) {
                $fullPath = storage_path('app/public/' . $pilihan->gambar_jawaban);
                echo "  File Exists: " . (file_exists($fullPath) ? 'YES ✓' : 'NO ✗') . PHP_EOL;
                if (file_exists($fullPath)) {
                    echo "  File Size: " . number_format(filesize($fullPath) / 1024, 2) . " KB" . PHP_EOL;
                }
            }
        }
    } else {
        echo "No pilihan jawaban found" . PHP_EOL;
    }
    
    echo PHP_EOL;
    echo "=== JSON DATA FOR DEBUG ===" . PHP_EOL;
    echo "gambar_pertanyaan: " . json_encode($soal->gambar_pertanyaan) . PHP_EOL;
    echo "pilihan_jawaban: " . json_encode($soal->pilihanJawaban->pluck('gambar_jawaban', 'label')->toArray()) . PHP_EOL;
} else {
    echo "No soal found in database" . PHP_EOL;
}
