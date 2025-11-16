<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataPelajaran;
use Carbon\Carbon;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mataPelajaran = [
            [
                'nama' => 'Matematika',
                'kode' => 'MTK',
                'deskripsi' => 'Mata Pelajaran Matematika untuk kelas 6 SD',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Bahasa Indonesia',
                'kode' => 'BIN',
                'deskripsi' => 'Mata Pelajaran Bahasa Indonesia untuk kelas 6 SD',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'IPA',
                'kode' => 'IPA',
                'deskripsi' => 'Mata Pelajaran Ilmu Pengetahuan Alam untuk kelas 6 SD',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'IPS',
                'kode' => 'IPS',
                'deskripsi' => 'Mata Pelajaran Ilmu Pengetahuan Sosial untuk kelas 6 SD',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'PKN',
                'kode' => 'PKN',
                'deskripsi' => 'Mata Pelajaran Pendidikan Kewarganegaraan untuk kelas 6 SD',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($mataPelajaran as $mp) {
            MataPelajaran::create($mp);
        }
    }
}
