<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $role = Role::query()->where('name', Role::SUPER_ADMIN)->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role_id' => $role->id,
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );
    }
}
