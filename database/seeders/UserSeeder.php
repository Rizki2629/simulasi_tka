<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Users
        $admins = [
            ['name' => 'Florence Shaw', 'email' => 'florence@untitledul.com', 'role' => 'admin'],
            ['name' => 'Amélie Laurent', 'email' => 'amelie@untitledul.com', 'role' => 'admin'],
        ];

        foreach ($admins as $admin) {
            User::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'role' => $admin['role'],
                'password' => Hash::make('password123'),
            ]);
        }

        // Guru Users
        $teachers = [
            ['name' => 'Ammar Foley', 'email' => 'ammar@untitledul.com', 'role' => 'guru'],
            ['name' => 'Caitlyn King', 'email' => 'caitlyn@untitledul.com', 'role' => 'guru'],
            ['name' => 'Sienna Hewitt', 'email' => 'sienna@untitledul.com', 'role' => 'guru'],
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher['name'],
                'email' => null,
                'role' => $teacher['role'],
                'password' => Hash::make('password123'),
            ]);
        }

        // Siswa Users
        $students = [
            // Kelas 6A
            ['name' => 'Ahmad Rizki Pratama', 'role' => 'siswa', 'nisn' => '0051234567', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2013-01-15', 'rombongan_belajar' => '6A'],
            ['name' => 'Siti Nurhaliza', 'role' => 'siswa', 'nisn' => '0051234568', 'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '2013-02-20', 'rombongan_belajar' => '6A'],
            ['name' => 'Budi Santoso', 'role' => 'siswa', 'nisn' => '0051234569', 'tempat_lahir' => 'Surabaya', 'tanggal_lahir' => '2013-03-10', 'rombongan_belajar' => '6A'],
            ['name' => 'Dewi Lestari', 'role' => 'siswa', 'nisn' => '0051234570', 'tempat_lahir' => 'Yogyakarta', 'tanggal_lahir' => '2013-04-05', 'rombongan_belajar' => '6A'],
            ['name' => 'Eko Prasetyo', 'role' => 'siswa', 'nisn' => '0051234571', 'tempat_lahir' => 'Semarang', 'tanggal_lahir' => '2013-05-12', 'rombongan_belajar' => '6A'],
            
            // Kelas 6B
            ['name' => 'Fitri Handayani', 'role' => 'siswa', 'nisn' => '0051234572', 'tempat_lahir' => 'Medan', 'tanggal_lahir' => '2013-06-18', 'rombongan_belajar' => '6B'],
            ['name' => 'Gilang Ramadhan', 'role' => 'siswa', 'nisn' => '0051234573', 'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '2013-07-22', 'rombongan_belajar' => '6B'],
            ['name' => 'Hana Safitri', 'role' => 'siswa', 'nisn' => '0051234574', 'tempat_lahir' => 'Makassar', 'tanggal_lahir' => '2013-08-14', 'rombongan_belajar' => '6B'],
            ['name' => 'Indra Kusuma', 'role' => 'siswa', 'nisn' => '0051234575', 'tempat_lahir' => 'Denpasar', 'tanggal_lahir' => '2013-09-08', 'rombongan_belajar' => '6B'],
            ['name' => 'Jihan Azzahra', 'role' => 'siswa', 'nisn' => '0051234576', 'tempat_lahir' => 'Malang', 'tanggal_lahir' => '2013-10-25', 'rombongan_belajar' => '6B'],
            
            // Kelas 6C
            ['name' => 'Krisna Wijaya', 'role' => 'siswa', 'nisn' => '0051234577', 'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '2013-11-30', 'rombongan_belajar' => '6C'],
            ['name' => 'Lina Marlina', 'role' => 'siswa', 'nisn' => '0051234578', 'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '2013-12-17', 'rombongan_belajar' => '6C'],
            ['name' => 'Muhammad Farhan', 'role' => 'siswa', 'nisn' => '0051234579', 'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '2013-01-28', 'rombongan_belajar' => '6C'],
            ['name' => 'Nur Aini', 'role' => 'siswa', 'nisn' => '0051234580', 'tempat_lahir' => 'Depok', 'tanggal_lahir' => '2013-02-14', 'rombongan_belajar' => '6C'],
            ['name' => 'Oscar Ramadhan', 'role' => 'siswa', 'nisn' => '0051234581', 'tempat_lahir' => 'Padang', 'tanggal_lahir' => '2013-03-21', 'rombongan_belajar' => '6C'],
            
            // Kelas 6D
            ['name' => 'Putri Amelia', 'role' => 'siswa', 'nisn' => '0051234582', 'tempat_lahir' => 'Pontianak', 'tanggal_lahir' => '2013-04-19', 'rombongan_belajar' => '6D'],
            ['name' => 'Qori Ramadhan', 'role' => 'siswa', 'nisn' => '0051234583', 'tempat_lahir' => 'Banjarmasin', 'tanggal_lahir' => '2013-05-26', 'rombongan_belajar' => '6D'],
            ['name' => 'Rina Susanti', 'role' => 'siswa', 'nisn' => '0051234584', 'tempat_lahir' => 'Balikpapan', 'tanggal_lahir' => '2013-06-11', 'rombongan_belajar' => '6D'],
            ['name' => 'Surya Pratama', 'role' => 'siswa', 'nisn' => '0051234585', 'tempat_lahir' => 'Manado', 'tanggal_lahir' => '2013-07-04', 'rombongan_belajar' => '6D'],
            ['name' => 'Tara Kusuma', 'role' => 'siswa', 'nisn' => '0051234586', 'tempat_lahir' => 'Pekanbaru', 'tanggal_lahir' => '2013-08-23', 'rombongan_belajar' => '6D'],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => null,
                'role' => $student['role'],
                'nisn' => $student['nisn'],
                'tempat_lahir' => $student['tempat_lahir'],
                'tanggal_lahir' => $student['tanggal_lahir'],
                'rombongan_belajar' => $student['rombongan_belajar'],
                'password' => Hash::make('password123'),
            ]);
        }
    }
}

