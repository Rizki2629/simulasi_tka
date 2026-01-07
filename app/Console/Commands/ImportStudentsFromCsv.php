<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ImportStudentsFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:students';

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
        $file = 'C:\Users\acer\simulasi_tka\DATA SISWA KELAS 6.csv';

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("Could not open file: $file");
            return;
        }

        // Get headers (semicolon delimiter)
        $headers = fgetcsv($handle, 1000, ';');
        
        $count = 0;
        $this->info("Starting import...");

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            // Map CSV columns (index based on file view)
            // 0:No, 1:Nama, 2:NIPD, 3:JK, 4:NISN, 5:Tempat Lahir, 6:Tanggal Lahir
            // 7:NIK, 8:Agama, 9:Alamat, 10:RT, 11:RW, 12:Dusun, 13:Kelurahan, 14:Kecamatan
            // 15:Kode Pos, 16:HP, 17:E-Mail, 18:Nama Ayah, 19:Nama Ibu, 20:Rombel Saat Ini

            $nama = $data[1];
            $nipd = $data[2];
            $jk = $data[3];
            $nisn = $data[4];
            $tempat_lahir = $data[5];
            $tanggal_lahir_raw = $data[6]; // dd/mm/yyyy
            $nik = $data[7];
            $agama = $data[8];
            $alamat = $data[9];
            $rt = $data[10];
            $rw = $data[11];
            $dusun = $data[12];
            $kelurahan = $data[13];
            $kecamatan = $data[14];
            $kode_pos = $data[15];
            $no_hp = $data[16];
            $email = $data[17];
            $nama_ayah = $data[18];
            $nama_ibu = $data[19];
            $rombel = $data[20];

            // Format Date
            try {
                $tanggal_lahir = Carbon::createFromFormat('d/m/Y', $tanggal_lahir_raw)->format('Y-m-d');
            } catch (\Exception $e) {
                $tanggal_lahir = null;
                $this->warn("Invalid date format for $nama: $tanggal_lahir_raw");
            }

            // Fallback Email if empty
            if (empty($email) || $email == '-') {
                $email = $nisn . '@student.sekolah.id';
            }

            // Create/Update User
            User::updateOrCreate(
                ['nisn' => $nisn], 
                [
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make('password'), // Default password
                    'role' => 'student',
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
}
