<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SqliteMergeToSql extends Command
{
    protected $signature = 'sqlite:merge-to-sql
        {--path=database.sqlite : Path to SQLite DB file (relative to base_path if not absolute)}
        {--tables= : Comma-separated table names (default: users,mata_pelajaran,simulasi,simulasi_peserta,nilai)}
        {--target-connection= : Target SQL connection (default: app default connection)}
        {--limit= : Optional per-table limit (for testing)}
        {--mode=insert-only : insert-only (default) or upsert}';

    protected $description = 'Merge data from a SQLite file into the main SQL database. Intended for filling gaps when legacy data exists only in SQLite.';

    public function handle(): int
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->error('PHP extension pdo_sqlite tidak tersedia; tidak bisa membaca SQLite.');
            return self::FAILURE;
        }

        $tablesOpt = trim((string) $this->option('tables'));
        $tables = $tablesOpt !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $tablesOpt))))
            : ['users', 'mata_pelajaran', 'simulasi', 'simulasi_peserta', 'nilai'];

        $targetConnection = (string) $this->option('target-connection');
        $targetConnection = $targetConnection !== '' ? $targetConnection : null;

        $limit = $this->option('limit');
        $limit = $limit !== null ? (int) $limit : null;

        $mode = strtolower(trim((string) $this->option('mode')));
        $mode = $mode !== '' ? $mode : 'insert-only';
        if (!in_array($mode, ['insert-only', 'upsert'], true)) {
            $this->error('Mode tidak valid. Pakai: insert-only atau upsert');
            return self::FAILURE;
        }

        $pathOpt = trim((string) $this->option('path'));
        $path = $this->resolvePath($pathOpt !== '' ? $pathOpt : 'database.sqlite');
        if (!is_file($path)) {
            $this->error('File SQLite tidak ditemukan: ' . $path);
            return self::FAILURE;
        }

        $this->info('SQLite → SQL merge');
        $this->info('SQLite file: ' . $path);
        $this->info('Mode: ' . $mode);
        $this->info('Tables: ' . implode(', ', $tables));

        $this->configureSqliteImportConnection($path);
        DB::purge('sqlite_import');

        $srcDb = DB::connection('sqlite_import');
        $srcSchema = Schema::connection('sqlite_import');

        $targetDb = $targetConnection ? DB::connection($targetConnection) : DB::connection();
        $targetSchema = Schema::connection($targetDb->getName());

        foreach ($tables as $table) {
            $this->line('');
            $this->mergeTable($srcDb, $srcSchema, $targetDb, $targetSchema, $table, $limit, $mode);
        }

        $this->line('');
        $this->info('Done.');
        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return base_path('database.sqlite');
        }

        // Windows absolute (C:\...) or UNC (\\server\share)
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1 || str_starts_with($path, '\\\\')) {
            return $path;
        }

        // Unix absolute
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private function configureSqliteImportConnection(string $path): void
    {
        config([
            'database.connections.sqlite_import' => [
                'driver' => 'sqlite',
                'database' => $path,
                'prefix' => '',
                // We are reading legacy data; disabling FK constraints helps avoid read-time issues
                'foreign_key_constraints' => false,
            ],
        ]);
    }

    private function mergeTable($srcDb, $srcSchema, $targetDb, $targetSchema, string $table, ?int $limit, string $mode): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            $this->warn('Skip invalid table name: ' . $table);
            return;
        }

        if (!$srcSchema->hasTable($table)) {
            $this->warn('Skip missing SQLite table: ' . $table);
            return;
        }

        if (!$targetSchema->hasTable($table)) {
            $this->warn('Skip missing target SQL table: ' . $table);
            return;
        }

        $query = $srcDb->table($table);
        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $rows = $query->get();
        $count = is_countable($rows) ? count($rows) : 0;
        $this->info("{$table}: {$count} rows (sqlite)");

        if ($count === 0) {
            return;
        }

        $targetColumns = $targetSchema->getColumnListing($table);
        $targetColumnsSet = array_fill_keys($targetColumns, true);
        $uniqueKeys = $this->uniqueKeyForTable($table);

        $inserted = 0;
        $existing = 0;
        $skipped = 0;

        foreach ($rows as $rowObj) {
            $row = is_object($rowObj) ? (array) $rowObj : (array) $rowObj;

            $payload = [];
            foreach ($row as $k => $v) {
                if (!is_string($k) || $k === '') {
                    continue;
                }
                $payload[$k] = $v;
            }

            // Keep only known target columns
            $payload = array_intersect_key($payload, $targetColumnsSet);

            // Ensure timestamps if target expects them
            $now = Carbon::now()->toDateTimeString();
            if (isset($targetColumnsSet['created_at']) && !array_key_exists('created_at', $payload)) {
                $payload['created_at'] = $now;
            }
            if (isset($targetColumnsSet['updated_at']) && !array_key_exists('updated_at', $payload)) {
                $payload['updated_at'] = $now;
            }

            $where = [];
            foreach ($uniqueKeys as $k) {
                if (!array_key_exists($k, $payload)) {
                    $skipped++;
                    continue 2;
                }
                $where[$k] = $payload[$k];
            }

            try {
                if ($mode === 'insert-only') {
                    $exists = $targetDb->table($table)->where($where)->exists();
                    if ($exists) {
                        $existing++;
                        continue;
                    }
                    $targetDb->table($table)->insert($payload);
                    $inserted++;
                } else {
                    $targetDb->table($table)->updateOrInsert($where, $payload);
                    $inserted++;
                }
            } catch (QueryException $e) {
                $skipped++;
                if ($skipped <= 5) {
                    $this->warn("{$table}: skip row due to DB constraint: " . $e->getCode());
                }
                continue;
            }
        }

        $msg = "{$table}: inserted {$inserted}";
        if ($mode === 'insert-only') {
            $msg .= ", existing {$existing}";
        }
        if ($skipped > 0) {
            $msg .= ", skipped {$skipped}";
        }
        $this->info($msg);
    }

    /**
     * @return array<int, string>
     */
    private function uniqueKeyForTable(string $table): array
    {
        return match ($table) {
            'nilai' => ['user_id', 'simulasi_id'],
            'simulasi_peserta' => ['simulasi_id', 'user_id'],
            'simulasi_soal' => ['simulasi_id', 'soal_id'],
            default => ['id'],
        };
    }
}
