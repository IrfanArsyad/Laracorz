<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Role::query()->updateOrCreate(
            ['name' => Role::SUPER_ADMIN],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh ke semua modul dan fitur sistem.',
                'is_active' => true,
                'read' => [Role::WILDCARD],
                'create' => [Role::WILDCARD],
                'update' => [Role::WILDCARD],
                'delete' => [Role::WILDCARD],
                'extra' => [],
            ],
        );
    }
}
