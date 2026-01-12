<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillSimulasiPeserta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example:
     * php artisan simulasi:backfill-peserta 2 --class=6A
     */
    protected $signature = 'simulasi:backfill-peserta
                            {simulasiId : ID simulasi}
                            {--class= : Filter rombongan belajar (contoh: 6A)}
                            {--from=sessions : Sumber peserta: sessions|nilai|class}
                            {--force : Lewati konfirmasi}';

    protected $description = 'Backfill data peserta ke tabel simulasi_peserta untuk simulasi yang sudah terlanjur dibuat';

    public function handle(): int
    {
        if (!Schema::hasTable('simulasi_peserta')) {
            $this->error('Tabel simulasi_peserta tidak ditemukan. Pastikan migration sudah dijalankan.');
            return 1;
        }

        $simulasiId = (int) $this->argument('simulasiId');
        $class = $this->option('class');
        $from = strtolower(trim((string) $this->option('from')));

        if ($class !== null && $class !== '') {
            $from = 'class';
        }

        if (!in_array($from, ['sessions', 'nilai', 'class'], true)) {
            $this->error("Nilai --from tidak valid: {$from}. Gunakan sessions|nilai|class");
            return 1;
        }

        $userIds = collect();

        if ($from === 'sessions') {
            if (!Schema::hasTable('exam_sessions')) {
                $this->error('Tabel exam_sessions tidak ditemukan.');
                return 1;
            }

            $userIds = DB::table('exam_sessions')
                ->where('simulasi_id', $simulasiId)
                ->pluck('user_id');
        }

        if ($from === 'nilai') {
            if (!Schema::hasTable('nilai')) {
                $this->error('Tabel nilai tidak ditemukan.');
                return 1;
            }

            $userIds = DB::table('nilai')
                ->where('simulasi_id', $simulasiId)
                ->pluck('user_id');
        }

        if ($from === 'class') {
            $classValue = strtoupper(trim((string) $class));
            $userIds = User::query()
                ->where('role', 'siswa')
                ->whereRaw('UPPER(COALESCE(rombongan_belajar, \'\')) = ?', [$classValue])
                ->pluck('id');
        }

        $userIds = $userIds
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values();

        // Defensive: only insert students
        $userIds = User::query()
            ->where('role', 'siswa')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            $this->warn('Tidak ada peserta yang ditemukan untuk di-backfill.');
            return 0;
        }

        $existingCount = DB::table('simulasi_peserta')
            ->where('simulasi_id', $simulasiId)
            ->count();

        $this->info("Simulasi: {$simulasiId}");
        $this->info("Mode: {$from}" . ($from === 'class' ? " (kelas {$class})" : ''));
        $this->info("Peserta kandidat: {$userIds->count()} siswa");
        $this->info("Peserta existing di simulasi_peserta: {$existingCount}");

        if (!$this->option('force')) {
            $confirmed = $this->confirm('Lanjutkan backfill (insertOrIgnore) ke simulasi_peserta?', false);
            if (!$confirmed) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        $now = now();
        $rows = $userIds
            ->map(fn ($id) => [
                'simulasi_id' => $simulasiId,
                'user_id' => $id,
                'status' => 'belum_mulai',
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'nilai' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        DB::table('simulasi_peserta')->insertOrIgnore($rows);

        $finalCount = DB::table('simulasi_peserta')
            ->where('simulasi_id', $simulasiId)
            ->count();

        $this->info("Selesai. Total peserta simulasi {$simulasiId} sekarang: {$finalCount}");

        return 0;
    }
}
