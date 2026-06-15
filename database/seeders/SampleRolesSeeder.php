<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use App\Services\ModuleRegistry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * 5 role contoh dengan permission berbeda — supaya bisa test matrix permission
 * & filter role di Manajemen Pengguna.
 */
class SampleRolesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Map name → id supaya bisa assign permission per modul
        $modules = Module::query()->pluck('id', 'name');

        $id = fn (string $name) => (int) ($modules[$name] ?? 0);

        // 1. Admin — semua kecuali settings & system-log
        Role::query()->updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Akses penuh ke modul user, role, module management, dan log admin.',
                'is_active' => true,
                'read' => [
                    $id('dashboard'),
                    $id('user-management'),
                    $id('role-management'),
                    $id('module-management'),
                    $id('admin-log'),
                ],
                'create' => [$id('user-management'), $id('role-management'), $id('module-management')],
                'update' => [$id('user-management'), $id('role-management'), $id('module-management')],
                'delete' => [$id('user-management'), $id('role-management')],
                'extra' => [],
            ],
        );

        // 2. Manager — view + approve di user, read role/module/log
        Role::query()->updateOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description' => 'Pantau user dan log, bisa update status user tapi tidak hapus.',
                'is_active' => true,
                'read' => [
                    $id('dashboard'),
                    $id('user-management'),
                    $id('role-management'),
                    $id('admin-log'),
                ],
                'create' => [],
                'update' => [$id('user-management')],
                'delete' => [],
                'extra' => [],
            ],
        );

        // 3. Editor — kelola user (CRUD) tapi tidak role/module
        Role::query()->updateOrCreate(
            ['name' => 'editor'],
            [
                'display_name' => 'Editor',
                'description' => 'Kelola pengguna (tambah/ubah/hapus). Tidak bisa mengakses role atau modul.',
                'is_active' => true,
                'read' => [$id('dashboard'), $id('user-management')],
                'create' => [$id('user-management')],
                'update' => [$id('user-management')],
                'delete' => [$id('user-management')],
                'extra' => [],
            ],
        );

        // 4. Staff — view user + ubah profil sendiri (basic)
        Role::query()->updateOrCreate(
            ['name' => 'staff'],
            [
                'display_name' => 'Staff',
                'description' => 'Akses dasar: dashboard + lihat daftar pengguna.',
                'is_active' => true,
                'read' => [$id('dashboard'), $id('user-management')],
                'create' => [],
                'update' => [],
                'delete' => [],
                'extra' => [],
            ],
        );

        // 5. Viewer — read-only dashboard saja
        Role::query()->updateOrCreate(
            ['name' => 'viewer'],
            [
                'display_name' => 'Viewer',
                'description' => 'Hanya bisa lihat dashboard. Tidak ada modul lain.',
                'is_active' => true,
                'read' => [$id('dashboard')],
                'create' => [],
                'update' => [],
                'delete' => [],
                'extra' => [],
            ],
        );

        // Flush cache supaya perubahan role kebaca immediate
        app(ModuleRegistry::class)->flush();
    }
}
