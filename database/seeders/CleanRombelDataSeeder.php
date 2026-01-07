<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class CleanRombelDataSeeder extends Seeder
{
    public function run()
    {
        // Remove "KELAS " prefix from all rombongan_belajar values
        User::where('role', 'siswa')
            ->where('rombongan_belajar', 'LIKE', 'KELAS %')
            ->get()
            ->each(function ($user) {
                $user->rombongan_belajar = str_replace('KELAS ', '', $user->rombongan_belajar);
                $user->save();
            });
            
        echo "Cleaned " . User::where('role', 'siswa')->whereNotNull('rombongan_belajar')->count() . " student records.\n";
    }
}
