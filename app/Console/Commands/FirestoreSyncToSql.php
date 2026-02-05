<?php

namespace App\Console\Commands;

use App\Services\FirestoreRestClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FirestoreSyncToSql extends Command
{
    protected $signature = 'firestore:sync-to-sql
        {--tables= : Comma-separated collection/table names (default: users,mata_pelajaran,simulasi,simulasi_peserta,nilai)}
        {--connection= : Target SQL connection (default: app default connection)}
        {--limit= : Optional per-table limit (for testing)}
        {--dry-run : Do not write; only show counts}
        {--fix-sequences : Fix Postgres sequences after importing explicit IDs (default: on for pgsql)}';

    protected $description = 'Import Firestore collections into SQL tables (upsert). Useful when production uses Postgres but source-of-truth is Firestore.';

    public function handle(FirestoreRestClient $firestore): int
    {
        $tablesOpt = trim((string) $this->option('tables'));
        $tables = $tablesOpt !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $tablesOpt))))
            : ['users', 'mata_pelajaran', 'simulasi', 'simulasi_peserta', 'nilai'];

        $connection = (string) $this->option('connection');
        $connection = $connection !== '' ? $connection : null;

        $limit = $this->option('limit');
        $limit = $limit !== null ? (int) $limit : null;
        $dryRun = (bool) $this->option('dry-run');

        $db = $connection ? DB::connection($connection) : DB::connection();
        $schema = Schema::connection($db->getName());
        $driver = (string) $db->getDriverName();

        $fixSequences = $this->option('fix-sequences');
        $shouldFixSequences = $fixSequences !== null
            ? (bool) $fixSequences
            : ($driver === 'pgsql');

        $this->info('Firestore → SQL sync' . ($dryRun ? ' (dry-run)' : ''));
        $this->info('SQL driver: ' . $driver . ($connection ? ' | connection=' . $connection : ''));
        $this->info('Tables: ' . implode(', ', $tables));

        foreach ($tables as $table) {
            $this->line('');
            $this->syncTable($firestore, $db, $schema, $driver, $table, $limit, $dryRun);
        }

        if (!$dryRun && $driver === 'pgsql' && $shouldFixSequences) {
            $this->line('');
            $this->info('Fixing Postgres sequences...');
            foreach ($tables as $table) {
                $this->fixPostgresSequence($db, $schema, $table);
            }
        }

        $this->line('');
        $this->info('Done.');
        return self::SUCCESS;
    }

    private function syncTable(FirestoreRestClient $firestore, $db, $schema, string $driver, string $table, ?int $limit, bool $dryRun): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            $this->warn('Skip invalid table name: ' . $table);
            return;
        }

        if (!$schema->hasTable($table)) {
            $this->warn('Skip missing SQL table: ' . $table);
            return;
        }

        $rows = $firestore->listCollection($table, $limit);
        $count = count($rows);
        $this->info("{$table}: {$count} docs");

        if ($count === 0 || $dryRun) {
            return;
        }

        $columns = $schema->getColumnListing($table);
        $columnsSet = array_fill_keys($columns, true);

        $uniqueKeys = $this->uniqueKeyForTable($table);

        $synced = 0;
        $skipped = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $payload = $this->normalizeRow($row);

            // Keep only known SQL columns
            $payload = array_intersect_key($payload, $columnsSet);

            // Ensure timestamps if columns exist
            $now = Carbon::now()->toDateTimeString();
            if (isset($columnsSet['created_at']) && !array_key_exists('created_at', $payload)) {
                $payload['created_at'] = $now;
            }
            if (isset($columnsSet['updated_at']) && !array_key_exists('updated_at', $payload)) {
                $payload['updated_at'] = $now;
            }

            $where = [];
            foreach ($uniqueKeys as $k) {
                if (!array_key_exists($k, $payload) && array_key_exists($k, $row)) {
                    $payload[$k] = $this->normalizeValue($row[$k]);
                }
                if (!array_key_exists($k, $payload)) {
                    // Missing unique key -> skip
                    continue 2;
                }
                $where[$k] = $payload[$k];
            }

            try {
                $db->table($table)->updateOrInsert($where, $payload);
            } catch (QueryException $e) {
                $skipped++;
                if ($skipped <= 5) {
                    $this->warn("{$table}: skip row due to DB constraint: " . $e->getCode());
                }
                continue;
            }

            $synced++;
            if ($synced % 500 === 0) {
                $this->line("  synced {$synced}/{$count} ...");
            }
        }

        $msg = "{$table}: synced {$synced} rows";
        if ($skipped > 0) {
            $msg .= " (skipped {$skipped})";
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

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            $out[$key] = $this->normalizeValue($value);
        }
        return $out;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateTimeString();
        }

        if (is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }

    private function fixPostgresSequence($db, $schema, string $table): void
    {
        if (!$schema->hasColumn($table, 'id')) {
            return;
        }

        // Works even if table isn't backed by a serial/bigserial (returns NULL sequence)
        $sql = "SELECT pg_get_serial_sequence(?, 'id') as seq";
        $seqRow = $db->selectOne($sql, [$table]);
        $seq = is_object($seqRow) ? (string) ($seqRow->seq ?? '') : '';
        if ($seq === '') {
            return;
        }

        $db->statement(
            "SELECT setval(?, (SELECT COALESCE(MAX(id), 1) FROM \"{$table}\"), true)",
            [$seq]
        );
    }
}
