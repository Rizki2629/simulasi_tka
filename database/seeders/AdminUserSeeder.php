<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin user already exists
        $admin = User::where('email', 'admin')->first();
        
        if ($admin) {
            // Update existing admin user
            $admin->update([
                'name' => 'Admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]);
            echo "Admin user updated successfully!\n";
        } else {
            // Create new admin user
            User::create([
                'name' => 'Admin',
                'email' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]);
            echo "Admin user created successfully!\n";
        }
        
        echo "Login credentials:\n";
        echo "Email: admin\n";
        echo "Password: admin\n";
    }
}
