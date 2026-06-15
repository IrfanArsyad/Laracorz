<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreMenuSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,

            // Sample data — comment kalau gak mau di-seed di production
            SampleRolesSeeder::class,
            SampleUsersSeeder::class,
        ]);
    }
}
