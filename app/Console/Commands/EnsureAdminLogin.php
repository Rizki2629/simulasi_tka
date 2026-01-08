<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminLogin extends Command
{
    protected $signature = 'auth:ensure-admin {--username=admin} {--password=admin}';
    protected $description = 'Pastikan ada akun admin yang bisa login (default: admin/admin)';

    public function handle(): int
    {
        $username = (string) $this->option('username');
        $password = (string) $this->option('password');

        if ($username === '' || $password === '') {
            $this->error('username/password tidak boleh kosong');
            return self::FAILURE;
        }

        $admin = User::firstOrNew(['email' => $username]);
        $admin->name = $admin->name ?: 'Admin';
        $admin->role = $admin->role ?: 'admin';
        $admin->password = Hash::make($password);
        $admin->save();

        $this->info('OK. Admin login siap.');
        $this->line('Username: ' . $username);
        $this->line('Password: ' . $password);

        return self::SUCCESS;
    }
}
