<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 10 user contoh — disebar ke 5 role berbeda + bermacam status,
 * supaya filter role/status di Manajemen Pengguna ada datanya.
 *
 * Semua pakai password sama: "password"
 */
class SampleUsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $password = Hash::make('password');

        $roles = Role::query()->pluck('id', 'name');
        $roleId = fn (string $name) => (int) ($roles[$name] ?? 0);

        $samples = [
            // Admin (2)
            ['name' => 'Budi Santoso',     'username' => 'budi',     'email' => 'budi@example.com',     'role' => 'admin',   'status' => 'active',   'last_login_at' => now()->subHours(2)],
            ['name' => 'Siti Rahmawati',   'username' => 'siti',     'email' => 'siti@example.com',     'role' => 'admin',   'status' => 'active',   'last_login_at' => now()->subDays(1)],

            // Manager (2)
            ['name' => 'Agus Setiawan',    'username' => 'agus',     'email' => 'agus@example.com',     'role' => 'manager', 'status' => 'active',   'last_login_at' => now()->subDays(3)],
            ['name' => 'Dewi Lestari',     'username' => 'dewi',     'email' => 'dewi@example.com',     'role' => 'manager', 'status' => 'inactive', 'last_login_at' => now()->subMonths(1)],

            // Editor (2)
            ['name' => 'Rizky Pratama',    'username' => 'rizky',    'email' => 'rizky@example.com',    'role' => 'editor',  'status' => 'active',   'last_login_at' => now()->subHours(5)],
            ['name' => 'Nina Hartono',     'username' => 'nina',     'email' => 'nina@example.com',     'role' => 'editor',  'status' => 'active',   'last_login_at' => now()->subDays(2)],

            // Staff (3)
            ['name' => 'Hendra Wijaya',    'username' => 'hendra',   'email' => 'hendra@example.com',   'role' => 'staff',   'status' => 'active',   'last_login_at' => now()->subDays(7)],
            ['name' => 'Putri Anggraini',  'username' => 'putri',    'email' => 'putri@example.com',    'role' => 'staff',   'status' => 'active',   'last_login_at' => now()->subWeek()],
            ['name' => 'Tono Susilo',      'username' => 'tono',     'email' => 'tono@example.com',     'role' => 'staff',   'status' => 'banned',   'last_login_at' => now()->subMonths(2)],

            // Viewer (1)
            ['name' => 'Lia Marlina',      'username' => 'lia',      'email' => 'lia@example.com',      'role' => 'viewer',  'status' => 'active',   'last_login_at' => now()->subDays(14)],
        ];

        foreach ($samples as $row) {
            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'role_id' => $roleId($row['role']),
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'password' => $password,
                    'status' => $row['status'],
                    'last_login_at' => $row['last_login_at'],
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
