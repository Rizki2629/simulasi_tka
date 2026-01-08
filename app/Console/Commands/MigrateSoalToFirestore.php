<?php

namespace App\Console\Commands;

use App\Models\Soal;
use App\Services\SoalFirestoreSyncService;
use Illuminate\Console\Command;

class MigrateSoalToFirestore extends Command
{
    protected $signature = 'firestore:migrate-soal {--limit= : Batasi jumlah soal yang dimigrasi}';
    protected $description = 'Migrasi/sinkronisasi semua data Soal (paket + sub) dari DB lokal ke Firestore';

    public function handle(SoalFirestoreSyncService $sync): int
    {
        $limit = $this->option('limit');
        $limit = is_numeric($limit) ? (int) $limit : null;

        $query = Soal::query()->with([
            'mataPelajaran',
            'creator',
            'subSoal.pilihanJawaban',
            'pilihanJawaban',
        ]);

        if ($limit && $limit > 0) {
            $query->limit($limit);
        }

        $soals = $query->orderBy('id')->get();

        $this->info('Mulai migrasi Soal ke Firestore: ' . $soals->count() . ' data');

        $count = 0;
        foreach ($soals as $soal) {
            $sync->sync($soal);
            $count++;
        }

        $this->info('Selesai. Total tersinkron: ' . $count);
        return self::SUCCESS;
    }
}
