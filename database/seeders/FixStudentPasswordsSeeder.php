<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixStudentPasswordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students
        $students = User::where('role', 'siswa')->get();

        $fixed = 0;
        $skipped = 0;

        foreach ($students as $student) {
            // Check if password is already hashed (starts with $2y$)
            if (str_starts_with($student->password, '$2y$')) {
                $skipped++;
                continue;
            }

            // Hash the password (assume default is 'password123')
            $student->password = Hash::make('password123');
            $student->save();
            $fixed++;
        }

        echo "Fixed {$fixed} student passwords.\n";
        echo "Skipped {$skipped} already hashed passwords.\n";
        echo "Total students: " . $students->count() . "\n";
    }
}
