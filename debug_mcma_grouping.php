<?php

use App\Models\User;
use App\Models\Simulasi;
use App\Models\Soal;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Assume we want to check the latest Simulasi active
$simulasi = Simulasi::where('is_active', true)->latest()->first();

if (!$simulasi) {
    echo "No active simulasi found.\n";
    exit;
}

echo "Simulasi ID: " . $simulasi->id . "\n";
echo "Mata Pelajaran: " . $simulasi->mataPelajaran->nama . "\n";

// Get Soals in order
$simulasiSoals = $simulasi->simulasiSoal()->orderBy('urutan')->get();

echo "Total Soal (Parents) in Simulasi: " . $simulasiSoals->count() . "\n\n";

// We want to simulate the Controller Logic for the 3rd item (Index 2 if user means 3)
// But wait, the Controller FLATTENS the structure into a new collection `$soals`.
// We need to replicate that logic to see what Question 3 ends up being.

$soals = collect();

foreach ($simulasiSoals as $ss) {
    $soal = Soal::with(['subSoal' => function($q) {
        $q->orderBy('nomor_urut');
        $q->orderBy('id'); // Add secondary sort just in case
    }])->find($ss->soal_id);
    
    // Logic copy-paste from Controller
    if ($soal->subSoal && $soal->subSoal->count() > 0) {
        $subSoals = $soal->subSoal->values();
        $currentIndex = 0;
        $totalSub = $subSoals->count();
        
        echo "Parent Soal ID {$soal->id} has {$totalSub} SubSoals.\n";
        
        while ($currentIndex < $totalSub) {
            $currentSub = $subSoals[$currentIndex];
            $type = $currentSub->jenis_soal;
            
            // Check grouping
            $groupableTypes = ['benar_salah', 'mcma', 'pilihan_ganda_kompleks'];
            $isGroupable = in_array($type, $groupableTypes);
            
            if ($isGroupable) {
                // Start Group
                $groupItems = collect([$currentSub]);
                $nextIndex = $currentIndex + 1;
                
                $getNormalizedType = function($t) {
                    return ($t === 'pilihan_ganda_kompleks') ? 'mcma' : $t;
                };
                
                $currentNormalizedType = $getNormalizedType($type);
                
                echo "  - Starting Group Type: {$currentNormalizedType} at Sub Index {$currentIndex} (ID: {$currentSub->id})\n";
                
                while ($nextIndex < $totalSub) {
                    $nextSub = $subSoals[$nextIndex];
                    $nextNormalizedType = $getNormalizedType($nextSub->jenis_soal);
                    
                    if ($nextNormalizedType === $currentNormalizedType) {
                        $groupItems->push($nextSub);
                        echo "    - Added Sub Index {$nextIndex} (ID: {$nextSub->id}) Type: {$nextNormalizedType}\n";
                        $nextIndex++;
                    } else {
                        echo "    - Stopping Group. Next Type: {$nextNormalizedType} (ID: {$nextSub->id})\n";
                        break;
                    }
                }
                
                $soals->push([
                    'type' => 'grouped',
                    'count' => $groupItems->count(),
                    'items' => $groupItems->pluck('id')->toArray(),
                    'debug_types' => $groupItems->pluck('jenis_soal')->toArray(),
                    'pilihan_jawaban_counts' => $groupItems->map(fn($i) => $i->pilihanJawaban->count())->toArray()
                ]);
                $currentIndex = $nextIndex;
                
            } else {
                echo "  - Single Sub Found: Type '{$type}' at Index {$currentIndex} (ID: {$currentSub->id})\n";
                $soals->push(['type' => 'single_sub', 'id' => $currentSub->id, 'real_type' => $type]);
                $currentIndex++;
            }
        }
    } else {
        $soals->push(['type' => 'standard', 'id' => $soal->id]);
    }
}

// Inspect Item #3 (Index 2)
if (isset($soals[2])) {
    echo "\n------------------------------------------------\n";
    echo "INSPECTING SOAL NOMOR 3 (Index 2 in flattened list):\n";
    print_r($soals[2]);
} else {
    echo "Soal index 2 not found.\n";
}

// Dump all for sanity check
echo "\nFlattened List Overview:\n";
foreach ($soals as $idx => $s) {
    if (isset($s['type']) && $s['type'] == 'grouped') {
         echo "[$idx] Grouped ({$s['count']} items)\n";
    } else {
         echo "[$idx] " . ($s['type'] ?? 'unknown') . "\n";
    }
}
