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
                'email' => $teacher['email'],
                'role' => $teacher['role'],
                'password' => Hash::make('password123'),
            ]);
        }

        // Siswa Users
        $students = [
            ['name' => 'Olly Shroeder', 'email' => 'olly@untitledul.com', 'role' => 'siswa'],
            ['name' => 'Mathilde Lewis', 'email' => 'mathilde@untitledul.com', 'role' => 'siswa'],
            ['name' => 'Jaya Willis', 'email' => 'jaya@untitledul.com', 'role' => 'siswa'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah@untitledul.com', 'role' => 'siswa'],
            ['name' => 'Michael Brown', 'email' => 'michael@untitledul.com', 'role' => 'siswa'],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'role' => $student['role'],
                'password' => Hash::make('password123'),
            ]);
        }
    }
}

