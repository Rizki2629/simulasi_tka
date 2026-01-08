<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ImportStudentsFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:students
                            {path? : Path file CSV (default: DATA SISWA KELAS 6.csv)}
                            {--delete-old : Hapus semua data siswa lama sebelum import}
                            {--password=password123 : Password default untuk siswa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import student data from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('path') ?: base_path('DATA SISWA KELAS 6.csv');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        if ($this->option('delete-old')) {
            $this->deleteOldStudents();
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("Could not open file: $file");
            return;
        }

        // Get headers (semicolon delimiter)
        $headers = fgetcsv($handle, 1000, ';');
        if ($headers === false) {
            fclose($handle);
            $this->error('CSV header tidak ditemukan / file kosong.');
            return;
        }
        
        $count = 0;
        $this->info("Starting import...");

        $defaultPassword = (string) $this->option('password');

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            if (!is_array($data) || count($data) === 0) {
                continue;
            }

            // Pastikan minimal ada 21 kolom sesuai format file
            $data = array_pad($data, 21, null);

            // Map CSV columns (index based on file view)
            // 0:No, 1:Nama, 2:NIPD, 3:JK, 4:NISN, 5:Tempat Lahir, 6:Tanggal Lahir
            // 7:NIK, 8:Agama, 9:Alamat, 10:RT, 11:RW, 12:Dusun, 13:Kelurahan, 14:Kecamatan
            // 15:Kode Pos, 16:HP, 17:E-Mail, 18:Nama Ayah, 19:Nama Ibu, 20:Rombel Saat Ini

            $nama = trim((string) ($data[1] ?? ''));
            $nipd = trim((string) ($data[2] ?? ''));
            $jk = trim((string) ($data[3] ?? ''));
            $nisn = trim((string) ($data[4] ?? ''));
            $tempat_lahir = trim((string) ($data[5] ?? ''));
            $tanggal_lahir_raw = trim((string) ($data[6] ?? '')); // dd/mm/yyyy
            $nik = trim((string) ($data[7] ?? ''));
            $agama = trim((string) ($data[8] ?? ''));
            $alamat = trim((string) ($data[9] ?? ''));
            $rt = trim((string) ($data[10] ?? ''));
            $rw = trim((string) ($data[11] ?? ''));
            $dusun = trim((string) ($data[12] ?? ''));
            $kelurahan = trim((string) ($data[13] ?? ''));
            $kecamatan = trim((string) ($data[14] ?? ''));
            $kode_pos = trim((string) ($data[15] ?? ''));
            $no_hp = trim((string) ($data[16] ?? ''));
            $email = trim((string) ($data[17] ?? ''));
            $nama_ayah = trim((string) ($data[18] ?? ''));
            $nama_ibu = trim((string) ($data[19] ?? ''));
            $rombel = trim((string) ($data[20] ?? ''));

            if ($nisn === '') {
                $this->warn('Skip baris: NISN kosong untuk nama: ' . ($nama !== '' ? $nama : '(tanpa nama)'));
                continue;
            }

            // Format Date
            try {
                if ($tanggal_lahir_raw === '' || $tanggal_lahir_raw === '-') {
                    $tanggal_lahir = null;
                } else {
                    $tanggal_lahir = Carbon::createFromFormat('d/m/Y', $tanggal_lahir_raw)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $tanggal_lahir = null;
                $this->warn("Invalid date format for $nama: $tanggal_lahir_raw");
            }

            // Fallback Email if empty
            if (empty($email) || $email == '-') {
                $email = $nisn . '@student.sekolah.id';
            }

            // Hindari bentrok unique email
            $emailOwner = User::where('email', $email)->first();
            if ($emailOwner && (string) $emailOwner->nisn !== $nisn) {
                $this->warn("Email duplikat '$email' untuk NISN $nisn. Menggunakan email fallback." );
                $email = $nisn . '@student.sekolah.id';
            }

            // Create/Update User
            User::updateOrCreate(
                ['nisn' => $nisn], 
                [
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'role' => 'siswa',
                    'nipd' => $nipd,
                    'jenis_kelamin' => $jk,
                    'tempat_lahir' => $tempat_lahir,
                    'tanggal_lahir' => $tanggal_lahir,
                    'nik' => $nik,
                    'agama' => $agama,
                    'alamat' => $alamat,
                    'rt' => $rt,
                    'rw' => $rw,
                    'dusun' => $dusun,
                    'kelurahan' => $kelurahan,
                    'kecamatan' => $kecamatan,
                    'kode_pos' => $kode_pos,
                    'no_hp' => $no_hp,
                    'nama_ayah' => $nama_ayah,
                    'nama_ibu' => $nama_ibu,
                    'rombongan_belajar' => $rombel,
                    'email_verified_at' => now(),
                ]
            );

            $count++;
        }

        fclose($handle);
        $this->info("Import completed! $count students imported/updated.");
    }

    private function deleteOldStudents(): void
    {
        $this->info('Menghapus data siswa lama...');

        $isSqlite = DB::getDriverName() === 'sqlite';
        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        try {

            $studentQuery = User::query()->whereIn('role', ['siswa', 'student']);
            $studentIds = $studentQuery->pluck('id')->all();
            $studentEmails = $studentQuery->pluck('email')->filter()->all();

            if (count($studentIds) === 0) {
                $this->info('Tidak ada siswa lama untuk dihapus.');
                return;
            }

            // Hapus data terkait (jaga-jaga jika FK SQLite tidak aktif / ada FK rusak)
            DB::table('exam_sessions')->whereIn('user_id', $studentIds)->delete();
            DB::table('nilai')->whereIn('user_id', $studentIds)->delete();
            DB::table('jawaban_peserta')
                ->whereIn('simulasi_peserta_id', function ($q) use ($studentIds) {
                    $q->select('id')->from('simulasi_peserta')->whereIn('user_id', $studentIds);
                })
                ->delete();
            DB::table('simulasi_peserta')->whereIn('user_id', $studentIds)->delete();
            DB::table('sessions')->whereIn('user_id', $studentIds)->delete();
            if (count($studentEmails) > 0) {
                DB::table('password_reset_tokens')->whereIn('email', $studentEmails)->delete();
            }

            $deleted = User::whereIn('id', $studentIds)->delete();
            $this->info("Selesai. $deleted siswa lama dihapus.");
        } finally {
            if ($isSqlite) {
                DB::statement('PRAGMA foreign_keys = ON;');
            }
        }
    }
}
