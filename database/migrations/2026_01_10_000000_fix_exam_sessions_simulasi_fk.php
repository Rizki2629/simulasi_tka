<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exam_sessions')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('exam_sessions__new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('simulasi_id')->constrained('simulasi')->onDelete('cascade');
                $table->enum('status', ['logged_in', 'in_progress', 'reviewing', 'completed'])->default('logged_in');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('last_activity')->useCurrent();
                $table->timestamps();

                $table->unique(['user_id', 'simulasi_id']);
            });

            DB::statement(
                'INSERT INTO exam_sessions__new (id, user_id, simulasi_id, status, started_at, submitted_at, last_activity, created_at, updated_at) '
                .'SELECT id, user_id, simulasi_id, status, started_at, submitted_at, last_activity, created_at, updated_at FROM exam_sessions'
            );

            Schema::drop('exam_sessions');
            Schema::rename('exam_sessions__new', 'exam_sessions');

            Schema::enableForeignKeyConstraints();

            return;
        }

        // For other drivers, attempt to drop and recreate FK.
        try {
            Schema::table('exam_sessions', function (Blueprint $table) {
                $table->dropForeign(['simulasi_id']);
            });
        } catch (Throwable $e) {
            // Ignore if FK name differs or already missing.
        }

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->foreign('simulasi_id')->references('id')->on('simulasi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // No safe down migration for SQLite rebuild.
    }
};
