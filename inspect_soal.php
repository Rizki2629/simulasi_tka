<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\Soal::with('subSoal')->find(4);

if (!$s) {
    echo "Soal #4 not found\n";
    exit;
}

echo "Parent ID: {$s->id}\n";
$s = App\Models\Soal::with('subSoal')->find(4);

if (!$s) {
    echo "Soal #4 not found\n";
    exit;
}

foreach ([5, 6, 7] as $subId) {
    echo "\nFOUND SUB $subId\n";
    $sub = $s->subSoal->where('id', $subId)->first();
    if ($sub) {
        echo "  > Child ID: {$sub->id} | Type: {$sub->jenis_soal}\n";
        echo "    Text: {$sub->pertanyaan}\n";
        echo "    Full Pilihan: " . json_encode($sub->pilihanJawaban) . "\n";
    }
}
