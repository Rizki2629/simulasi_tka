<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sub_soal') || !Schema::hasTable('soal')) {
            return;
        }

        // We only need to ensure the FK points to the correct table ('soal').
        // The original issue was a FK referencing a non-existent 'soals' table.
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
                        $sql = <<<'SQL'
select
        c.conname as name,
        c.confrelid::regclass::text as ref_table
from pg_constraint c
join pg_class t on t.oid = c.conrelid
where t.relname = 'sub_soal'
    and c.conname = 'sub_soal_soal_id_foreign'
limit 1
SQL;

                        $row = DB::selectOne($sql);

            if ($row && isset($row->ref_table) && $row->ref_table === 'soal') {
                return;
            }

            DB::statement('ALTER TABLE sub_soal DROP CONSTRAINT IF EXISTS sub_soal_soal_id_foreign');
            DB::statement(
                'ALTER TABLE sub_soal ADD CONSTRAINT sub_soal_soal_id_foreign '
                . 'FOREIGN KEY (soal_id) REFERENCES soal(id) ON DELETE CASCADE'
            );

            return;
        }

        // Fallback for other drivers: attempt a best-effort drop/recreate.
        Schema::table('sub_soal', function (Blueprint $table) {
            try {
                $table->dropForeign(['soal_id']);
            } catch (\Throwable $e) {
                // Ignore if FK doesn't exist or driver doesn't support it cleanly.
            }
        });

        Schema::table('sub_soal', function (Blueprint $table) {
            $table->foreign('soal_id')->references('id')->on('soal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as this fixes a bug
    }
};
