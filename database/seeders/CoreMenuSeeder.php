<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoreMenuSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $groups = [
            ['name' => 'main', 'label' => 'Main', 'icon' => 'house', 'order' => 1],
            ['name' => 'user-access', 'label' => 'User & Access', 'icon' => 'shield-check', 'order' => 2],
            ['name' => 'system', 'label' => 'System', 'icon' => 'settings', 'order' => 3],
        ];

        $groupIds = [];
        foreach ($groups as $g) {
            $row = ModuleGroup::query()->updateOrCreate(
                ['name' => $g['name']],
                ['label' => $g['label'], 'icon' => $g['icon'], 'order' => $g['order'], 'active' => true],
            );
            $groupIds[$g['name']] = $row->id;
        }

        $this->upsertModule([
            'module_group_id' => $groupIds['main'],
            'parent_id' => null,
            'name' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'layout-dashboard',
            'url' => '/dashboard',
            'route_name' => 'dashboard',
            'order' => 1,
        ]);

        $this->upsertModule([
            'module_group_id' => $groupIds['user-access'],
            'parent_id' => null,
            'name' => 'user-management',
            'label' => 'Pengguna',
            'icon' => 'users',
            'url' => '/users',
            'route_name' => 'users.index',
            'order' => 1,
        ]);

        $rolePerm = $this->upsertModule([
            'module_group_id' => $groupIds['user-access'],
            'parent_id' => null,
            'name' => 'role-permission',
            'label' => 'Role & Permission',
            'icon' => 'shield',
            'url' => null,
            'route_name' => null,
            'order' => 2,
        ]);

        $this->upsertModule([
            'module_group_id' => null,
            'parent_id' => $rolePerm->id,
            'name' => 'role-management',
            'label' => 'Role',
            'icon' => 'user-cog',
            'url' => '/roles',
            'route_name' => 'roles.index',
            'order' => 1,
        ]);

        $this->upsertModule([
            'module_group_id' => null,
            'parent_id' => $rolePerm->id,
            'name' => 'module-management',
            'label' => 'Module',
            'icon' => 'puzzle',
            'url' => '/modules',
            'route_name' => 'modules.index',
            'order' => 2,
        ]);

        $this->upsertModule([
            'module_group_id' => $groupIds['system'],
            'parent_id' => null,
            'name' => 'admin-log',
            'label' => 'Admin Log',
            'icon' => 'clipboard-list',
            'url' => '/admin-logs',
            'route_name' => 'admin-logs.index',
            'order' => 1,
        ]);

        $this->upsertModule([
            'module_group_id' => $groupIds['system'],
            'parent_id' => null,
            'name' => 'system-log',
            'label' => 'System Log',
            'icon' => 'activity',
            'url' => '/system-logs',
            'route_name' => 'system-logs.index',
            'order' => 2,
        ]);

        $this->upsertModule([
            'module_group_id' => $groupIds['system'],
            'parent_id' => null,
            'name' => 'settings',
            'label' => 'Pengaturan',
            'icon' => 'sliders',
            'url' => '/settings',
            'route_name' => 'settings.index',
            'order' => 3,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertModule(array $data): Module
    {
        return Module::query()->updateOrCreate(
            ['name' => $data['name']],
            $data,
        );
    }
}
