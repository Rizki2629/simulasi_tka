<?php

namespace App\Console\Commands;

use App\Services\FirestoreRestClient;
use Illuminate\Console\Command;

class ImportSqlToFirestore extends Command
{
    protected $signature = 'firestore:import-sql
        {path? : Path file .sql (default: simulasi_tka.sql)}
        {--tables=* : Hanya import tabel tertentu (bisa diulang)}
        {--skip-tables=* : Skip tabel tertentu (bisa diulang)}
        {--limit= : Batasi total row per tabel (debug)}';

    protected $description = 'Import semua data INSERT dari file SQL dump ke Firestore (koleksi = nama tabel)';

    public function handle(FirestoreRestClient $firestore): int
    {
        $path = $this->argument('path') ?: base_path('simulasi_tka.sql');
        if (!is_string($path) || $path === '') {
            $this->error('Path SQL tidak valid');
            return self::FAILURE;
        }

        if (!is_file($path)) {
            $this->error('File tidak ditemukan: ' . $path);
            return self::FAILURE;
        }

        $tables = $this->option('tables');
        $skipTables = $this->option('skip-tables');
        $limitOpt = $this->option('limit');
        $limit = is_numeric($limitOpt) ? (int) $limitOpt : null;

        $tables = is_array($tables) ? array_values(array_filter(array_map('strval', $tables))) : [];
        $skipTables = is_array($skipTables) ? array_values(array_filter(array_map('strval', $skipTables))) : [];

        $this->info('Import SQL → Firestore');
        $this->info('File: ' . $path);
        $this->info('Firestore: project=' . (env('FIREBASE_PROJECT_ID') ?: '-') . ' database=' . (env('FIRESTORE_DATABASE_ID', '(default)')));

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->error('Gagal membuka file: ' . $path);
            return self::FAILURE;
        }

        $inInsert = false;
        $buffer = '';

        $tableCounts = [];
        $totalDocs = 0;

        try {
            while (($line = fgets($handle)) !== false) {
                $trim = ltrim($line);

                if (!$inInsert) {
                    if (str_starts_with($trim, 'INSERT INTO')) {
                        $inInsert = true;
                        $buffer = $line;

                        if (str_contains($line, ';')) {
                            $this->processInsert($firestore, $buffer, $tables, $skipTables, $limit, $tableCounts, $totalDocs);
                            $buffer = '';
                            $inInsert = false;
                        }
                    }

                    continue;
                }

                $buffer .= $line;

                if (str_contains($line, ';')) {
                    $this->processInsert($firestore, $buffer, $tables, $skipTables, $limit, $tableCounts, $totalDocs);
                    $buffer = '';
                    $inInsert = false;
                }
            }
        } finally {
            fclose($handle);
        }

        $this->newLine();
        $this->info('Selesai. Total dokumen: ' . $totalDocs);
        foreach ($tableCounts as $table => $count) {
            $this->line('- ' . $table . ': ' . $count);
        }

        return self::SUCCESS;
    }

    /**
     * @param array<int,string> $onlyTables
     * @param array<int,string> $skipTables
     * @param array<string,int> $tableCounts
     */
    private function processInsert(
        FirestoreRestClient $firestore,
        string $sql,
        array $onlyTables,
        array $skipTables,
        ?int $limit,
        array &$tableCounts,
        int &$totalDocs,
    ): void {
        $parsed = $this->parseInsertStatement($sql);
        if ($parsed === null) {
            return;
        }

        [$table, $columns, $rows] = $parsed;

        if (!empty($onlyTables) && !in_array($table, $onlyTables, true)) {
            return;
        }

        if (!empty($skipTables) && in_array($table, $skipTables, true)) {
            return;
        }

        $tableCounts[$table] = $tableCounts[$table] ?? 0;

        foreach ($rows as $rowValues) {
            if ($limit !== null && $tableCounts[$table] >= $limit) {
                break;
            }

            $doc = [];
            foreach ($columns as $idx => $col) {
                $doc[$col] = $rowValues[$idx] ?? null;
            }

            // Try to decode JSON columns when stored as string
            foreach ($doc as $k => $v) {
                if (!is_string($v)) {
                    continue;
                }
                $s = trim($v);
                if ($s === '' || ($s[0] !== '{' && $s[0] !== '[')) {
                    continue;
                }
                $decoded = json_decode($s, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $doc[$k] = $decoded;
                }
            }

            $docId = null;
            if (array_key_exists('id', $doc) && $doc['id'] !== null && $doc['id'] !== '') {
                $docId = (string) $doc['id'];
            }

            if ($docId === null) {
                $docId = sha1($table . '|' . ($tableCounts[$table] + 1) . '|' . json_encode($doc));
            }

            $firestore->setDocument($table, $docId, $doc);

            $tableCounts[$table]++;
            $totalDocs++;

            if ($totalDocs % 200 === 0) {
                $this->info('Progress: ' . $totalDocs . ' dokumen');
            }
        }

        $this->line('OK ' . $table . ' +' . $tableCounts[$table]);
    }

    /**
     * @return array{0:string,1:array<int,string>,2:array<int,array<int,mixed>>}|null
     */
    private function parseInsertStatement(string $sql): ?array
    {
        $sql = trim($sql);
        if (!str_starts_with(ltrim($sql), 'INSERT INTO')) {
            return null;
        }

        // Match: INSERT INTO `table` (`a`, `b`) VALUES (...),(...);
        $re = '/^INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*VALUES\s*(.*);\s*$/si';
        if (!preg_match($re, $sql, $m)) {
            return null;
        }

        $table = $m[1];
        $columnsRaw = $m[2];
        $valuesRaw = $m[3];

        $columns = array_map(function ($c) {
            $c = trim($c);
            $c = trim($c, "` \t\n\r\0\x0B");
            return $c;
        }, explode(',', $columnsRaw));

        $columns = array_values(array_filter($columns, fn($c) => $c !== ''));
        if (empty($columns)) {
            return null;
        }

        $rows = $this->parseValuesTuples($valuesRaw);
        if (empty($rows)) {
            return null;
        }

        return [$table, $columns, $rows];
    }

    /**
     * Parse values section: (1,'a'),(2,'b')
     *
     * @return array<int, array<int, mixed>>
     */
    private function parseValuesTuples(string $values): array
    {
        $values = trim($values);
        $len = strlen($values);
        $i = 0;

        $rows = [];

        $skipSpaces = function () use (&$i, $len, $values) {
            while ($i < $len) {
                $ch = $values[$i];
                if ($ch === ' ' || $ch === "\n" || $ch === "\r" || $ch === "\t") {
                    $i++;
                    continue;
                }
                break;
            }
        };

        while ($i < $len) {
            $skipSpaces();

            // skip commas between tuples
            if ($i < $len && $values[$i] === ',') {
                $i++;
                continue;
            }

            $skipSpaces();
            if ($i >= $len) {
                break;
            }

            if ($values[$i] !== '(') {
                // unknown token; stop
                break;
            }
            $i++; // (

            $row = [];
            while ($i < $len) {
                $skipSpaces();

                $row[] = $this->parseOneValue($values, $i);

                $skipSpaces();
                if ($i >= $len) {
                    break;
                }

                $ch = $values[$i];
                if ($ch === ',') {
                    $i++;
                    continue;
                }
                if ($ch === ')') {
                    $i++;
                    break;
                }

                // Unexpected char; try to recover
                if ($ch === "\n" || $ch === "\r" || $ch === "\t" || $ch === ' ') {
                    $i++;
                    continue;
                }

                // stop tuple
                break;
            }

            $rows[] = $row;

            $skipSpaces();
            if ($i < $len && $values[$i] === ',') {
                $i++;
            }
        }

        return $rows;
    }

    private function parseOneValue(string $s, int &$i): mixed
    {
        $len = strlen($s);
        if ($i >= $len) {
            return null;
        }

        // NULL
        if (strncasecmp(substr($s, $i, 4), 'NULL', 4) === 0) {
            $i += 4;
            return null;
        }

        // Quoted string
        if ($s[$i] === "'") {
            $i++;
            $out = '';
            while ($i < $len) {
                $ch = $s[$i];
                if ($ch === "'" ) {
                    $i++;
                    break;
                }
                if ($ch === '\\') {
                    $i++;
                    if ($i >= $len) {
                        break;
                    }
                    $esc = $s[$i];
                    $i++;
                    $out .= match ($esc) {
                        'n' => "\n",
                        'r' => "\r",
                        't' => "\t",
                        '0' => "\0",
                        'Z' => "\x1A",
                        "'" => "'",
                        '"' => '"',
                        '\\' => '\\',
                        default => $esc,
                    };
                    continue;
                }
                $out .= $ch;
                $i++;
            }
            return $out;
        }

        // Bare token until comma or )
        $start = $i;
        while ($i < $len) {
            $ch = $s[$i];
            if ($ch === ',' || $ch === ')') {
                break;
            }
            $i++;
        }

        $token = trim(substr($s, $start, $i - $start));
        if ($token === '') {
            return null;
        }

        // Numeric?
        if (preg_match('/^-?\d+$/', $token)) {
            return (int) $token;
        }
        if (preg_match('/^-?\d+\.\d+$/', $token)) {
            return (float) $token;
        }

        return $token;
    }
}
