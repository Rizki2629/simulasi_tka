<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPgSequences extends Command
{
    protected $signature = 'db:fix-pg-sequences {--dry-run : Tampilkan query tanpa mengeksekusi}';

    protected $description = 'Re-sync PostgreSQL sequences (SERIAL/BIGSERIAL) to MAX(id)+1 to avoid duplicate primary key inserts after SQL imports.';

    public function handle(): int
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->info('Skipped: DB driver is not pgsql.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        // Find all columns that use a sequence (SERIAL/BIGSERIAL).
        $columns = DB::select(
            "SELECT table_name, column_name"
            . " FROM information_schema.columns"
            . " WHERE table_schema = 'public'"
            . "   AND column_default LIKE 'nextval(%'"
            . " ORDER BY table_name, column_name"
        );

        if (empty($columns)) {
            $this->info('No serial columns found.');
            return self::SUCCESS;
        }

        $fixedCount = 0;
        foreach ($columns as $c) {
            $table = (string) ($c->table_name ?? '');
            $column = (string) ($c->column_name ?? '');
            if ($table === '' || $column === '') {
                continue;
            }

            // Values come from information_schema (trusted); still keep quoting for identifiers in MAX().
            $sql = "SELECT setval(pg_get_serial_sequence('{$table}', '{$column}'), "
                . "COALESCE((SELECT MAX(\"{$column}\") FROM \"{$table}\"),0)+1, false)";

            if ($dryRun) {
                $this->line($sql);
                $fixedCount++;
                continue;
            }

            try {
                DB::statement($sql);
                $fixedCount++;
            } catch (\Throwable $e) {
                $this->warn("Failed: {$table}.{$column} - {$e->getMessage()}");
            }
        }

        $this->info("Done. Synced sequences: {$fixedCount}");
        return self::SUCCESS;
    }
}
