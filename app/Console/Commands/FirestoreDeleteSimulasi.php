<?php

namespace App\Console\Commands;

use App\Services\FirestoreExamSessionStore;
use App\Services\FirestoreJawabanPesertaStore;
use App\Services\FirestoreNilaiStore;
use App\Services\FirestoreRestClient;
use App\Services\FirestoreSimulasiPesertaStore;
use App\Services\FirestoreSimulasiSoalStore;
use App\Services\FirestoreSimulasiStore;
use Illuminate\Console\Command;

class FirestoreDeleteSimulasi extends Command
{
    protected $signature = 'firestore:delete-simulasi
        {simulasiId : ID simulasi yang ingin dihapus di Firestore}
        {--dry-run : Hanya tampilkan estimasi yang akan dihapus}
        {--limit=5000 : Batas query per koleksi (keamanan)}';

    protected $description = 'Hapus data simulasi di Firestore berdasarkan simulasi_id (simulasi, simulasi_soal, peserta, sesi, nilai, jawaban).';

    public function handle(
        FirestoreRestClient $client,
        FirestoreSimulasiStore $simulasiStore,
        FirestoreSimulasiSoalStore $simulasiSoalStore,
        FirestoreSimulasiPesertaStore $pesertaStore,
        FirestoreExamSessionStore $examSessionStore,
        FirestoreNilaiStore $nilaiStore,
        FirestoreJawabanPesertaStore $jawabanStore,
    ): int {
        $simulasiIdRaw = $this->argument('simulasiId');
        $simulasiId = is_numeric($simulasiIdRaw) ? (int) $simulasiIdRaw : 0;
        if ($simulasiId <= 0) {
            $this->error('simulasiId harus angka > 0');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limitOpt = $this->option('limit');
        $limit = is_numeric($limitOpt) ? (int) $limitOpt : 5000;
        $limit = $limit > 0 ? $limit : 5000;

        $this->info('Firestore delete simulasi');
        $this->line('Project: ' . (env('FIREBASE_PROJECT_ID') ?: '-') . ' | DB: ' . (env('FIRESTORE_DATABASE_ID', '(default)')));
        $this->line('Simulasi ID: ' . $simulasiId);
        $this->line('Mode: ' . ($dryRun ? 'dry-run' : 'DELETE'));
        $this->line('Limit/query: ' . $limit);

        // Collect affected user IDs from several collections.
        $userIds = [];

        $pesertaRows = $pesertaStore->listBySimulasiId($simulasiId, $limit);
        foreach ($pesertaRows as $row) {
            $uid = (int) ($row['user_id'] ?? 0);
            if ($uid > 0) {
                $userIds[$uid] = true;
            }
        }

        $examRows = $examSessionStore->listBySimulasiId($simulasiId);
        foreach (array_keys($examRows) as $uid) {
            $uid = (int) $uid;
            if ($uid > 0) {
                $userIds[$uid] = true;
            }
        }

        $nilaiRows = $nilaiStore->listBySimulasiId($simulasiId, $limit);
        foreach ($nilaiRows as $row) {
            $uid = (int) ($row['user_id'] ?? 0);
            if ($uid > 0) {
                $userIds[$uid] = true;
            }
        }

        $userIdList = array_keys($userIds);
        sort($userIdList);

        // Additional counts (best-effort) for jawaban_peserta by simulasi_id
        $jawabanCount = 0;
        try {
            $jawabanRows = $client->runQueryEquals('jawaban_peserta', ['simulasi_id' => $simulasiId], $limit);
            $jawabanCount = count($jawabanRows);
        } catch (\Throwable $e) {
            // ignore
        }

        $this->newLine();
        $this->info('Estimasi data terkait');
        $this->line('- simulasi_peserta: ' . count($pesertaRows));
        $this->line('- exam_sessions: ' . count($examRows));
        $this->line('- nilai: ' . count($nilaiRows));
        $this->line('- jawaban_peserta (by simulasi_id): ' . $jawabanCount . ($jawabanCount >= $limit ? ' (kena limit)' : ''));
        $this->line('- affected users: ' . count($userIdList));

        if ($dryRun) {
            $preview = array_slice($userIdList, 0, 20);
            if (!empty($preview)) {
                $this->line('Sample user_id: ' . implode(', ', $preview) . (count($userIdList) > 20 ? ' ...' : ''));
            }
            $this->newLine();
            $this->warn('Dry-run selesai. Jalankan tanpa --dry-run untuk menghapus.');
            return self::SUCCESS;
        }

        // Do deletions.
        $deleted = [
            'jawaban_peserta' => 0,
            'nilai' => 0,
            'exam_sessions' => 0,
            'simulasi_peserta' => 0,
            'simulasi_soal' => 0,
            'simulasi' => 0,
        ];

        foreach ($userIdList as $uid) {
            try {
                $jawabanStore->deleteAllForUserSimulasi($uid, $simulasiId);
                // Can't easily know exact deleted count without re-query; keep best-effort.
            } catch (\Throwable $e) {
                // ignore per-user
            }

            try {
                $nilaiStore->delete($uid, $simulasiId);
                $deleted['nilai']++;
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $examSessionStore->delete($uid, $simulasiId);
                $deleted['exam_sessions']++;
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $pesertaStore->delete($uid, $simulasiId);
                $deleted['simulasi_peserta']++;
            } catch (\Throwable $e) {
                // ignore
            }
        }

        try {
            $deleted['simulasi_soal'] = $simulasiSoalStore->deleteAllBySimulasiId($simulasiId, $limit);
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $simulasiStore->deleteById($simulasiId);
            $deleted['simulasi'] = 1;
        } catch (\Throwable $e) {
            // ignore
        }

        // Best-effort: recount jawaban_peserta remaining (limit-bounded)
        try {
            $jawabanRowsAfter = $client->runQueryEquals('jawaban_peserta', ['simulasi_id' => $simulasiId], $limit);
            $deleted['jawaban_peserta'] = max(0, $jawabanCount - count($jawabanRowsAfter));
        } catch (\Throwable $e) {
            // ignore
        }

        $this->newLine();
        $this->info('Selesai (best-effort). Ringkasan:');
        foreach ($deleted as $k => $v) {
            $this->line('- ' . $k . ': ' . $v);
        }

        if ($jawabanCount >= $limit) {
            $this->warn('Catatan: jawaban_peserta kena limit. Jika data masih tersisa, jalankan lagi dengan --limit lebih besar.');
        }

        return self::SUCCESS;
    }
}
