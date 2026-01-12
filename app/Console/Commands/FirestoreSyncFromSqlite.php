<?php

namespace App\Console\Commands;

use App\Services\FirestoreRestClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FirestoreSyncFromSqlite extends Command
{
    protected $signature = 'firestore:sync-from-sqlite
        {--tables= : Comma-separated table names (default: all)}
        {--mode=upsert : upsert|append (default: upsert)}
        {--limit= : Optional per-table limit for testing}
        {--dry-run : Do not write; only show counts}';

    protected $description = 'Sync SQLite tables into Firestore collections using auto document IDs + field id.';

    public function handle(FirestoreRestClient $firestore): int
    {
        $mode = strtolower((string) $this->option('mode'));
        if (!in_array($mode, ['upsert', 'append'], true)) {
            $this->error("Invalid --mode={$mode}. Use upsert or append.");
            return self::FAILURE;
        }

        $tablesOpt = trim((string) $this->option('tables'));
        $tables = $tablesOpt !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $tablesOpt))))
            : $this->listSqliteTables();

        if ($tables === []) {
            $this->error('No tables found to sync.');
            return self::FAILURE;
        }

        $limit = $this->option('limit');
        $limit = $limit !== null ? (int) $limit : null;
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Mode: ' . $mode . ($dryRun ? ' (dry-run)' : ''));
        $this->info('Tables: ' . implode(', ', $tables));

        foreach ($tables as $table) {
            $this->line('');
            $this->syncTable($firestore, $table, $mode, $limit, $dryRun);
        }

        $this->line('');
        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function listSqliteTables(): array
    {
        $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
        $tables = [];
        foreach ($rows as $row) {
            $name = (string) ($row->name ?? '');
            if ($name !== '') {
                $tables[] = $name;
            }
        }
        return $tables;
    }

    private function syncTable(FirestoreRestClient $firestore, string $table, string $mode, ?int $limit, bool $dryRun): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            $this->warn("Skip invalid table name: {$table}");
            return;
        }

        $query = DB::table($table);
        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $rows = $query->get();
        $count = $rows->count();
        $this->info("{$table}: {$count} rows");

        if ($count === 0) {
            return;
        }

        if ($dryRun) {
            return;
        }

        $synced = 0;
        foreach ($rows as $rowObj) {
            $row = (array) $rowObj;
            $payload = $this->normalizeRow($row);

            // Ensure we always have a top-level field `id` (user requested)
            if (!array_key_exists('id', $payload)) {
                $payload['id'] = $this->fallbackIdForRow($table, $payload);
            }

            if ($mode === 'append') {
                $firestore->addDocument($table, $payload);
            } else {
                // upsert by `id` field
                $firestore->upsertByField($table, 'id', $payload['id'], $payload);
            }

            $synced++;
            if ($synced % 200 === 0) {
                $this->line("  synced {$synced}/{$count} ...");
            }
        }

        $this->info("{$table}: synced {$synced} documents");
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

            // Normalize common timestamp columns into Carbon so Firestore stores them as timestampValue
            if (in_array($key, ['created_at', 'updated_at', 'email_verified_at', 'expires_at', 'started_at', 'submitted_at', 'last_activity'], true)) {
                $out[$key] = $this->toCarbonOrNull($value);
                continue;
            }

            // Preserve ints/bools when possible
            $out[$key] = $this->normalizeScalar($value);
        }

        return $out;
    }

    private function normalizeScalar(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            // SQLite often returns numeric strings for integers
            if ($value !== '' && preg_match('/^-?\d+$/', $value)) {
                $int = (int) $value;
                // avoid converting big strings that might be IDs? keep simple
                return $int;
            }
            return $value;
        }

        // Fallback: json encode objects/arrays
        if (is_array($value)) {
            return $value;
        }

        return (string) $value;
    }

    private function toCarbonOrNull(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        try {
            return Carbon::parse($str);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fallbackIdForRow(string $table, array $row): string|int
    {
        // Prefer common unique columns
        foreach (['migration', 'token', 'email', 'nisn'] as $key) {
            $v = Arr::get($row, $key);
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        // sessions table has string primary key `id` normally; if missing, derive
        if ($table === 'sessions') {
            return (string) (Arr::get($row, 'id') ?? '');
        }

        // Last resort: stable hash of row
        return sha1($table . '|' . json_encode($row));
    }
}
