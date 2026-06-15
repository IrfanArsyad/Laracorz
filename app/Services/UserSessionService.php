<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Snapshot izin user dihitung sekali saat login dan disimpan di
 * session driver (database/file — bukan Redis). Selama session hidup,
 * semua cek izin & menu dibaca dari snapshot ini tanpa query DB.
 *
 * Efek samping: bila admin mengubah permission role yang sedang
 * login, user tersebut tetap pakai snapshot lama sampai logout/login
 * ulang. Ini disengaja — batas izin terkunci sejak login.
 */
class UserSessionService
{
    public const SESSION_KEY = 'auth.snapshot';

    /**
     * Build snapshot lengkap untuk user (permissions per module + daftar
     * module yang bisa diakses + struktur menu).
     *
     * @return array{role: array<string,mixed>|null, permissions: array<int,array<string,mixed>>, modules: array<int,array<string,mixed>>, menu: array<int,array<string,mixed>>, is_super_admin: bool}
     */
    public function build(User $user): array
    {
        $user->loadMissing('role');

        $role = $user->role;
        $isSuperAdmin = $role?->isSuperAdmin() ?? false;

        $allModules = Module::query()
            ->active()
            ->orderBy('order')
            ->get();

        $accessibleIds = $this->resolveAccessibleIds($role, $allModules);
        $accessible = $allModules->whereIn('id', $accessibleIds);

        $permissions = $this->buildPermissions($role, $allModules, $isSuperAdmin);

        $modules = $accessible
            ->map(fn (Module $m): array => [
                'id' => (int) $m->id,
                'name' => $m->name,
                'label' => $m->label,
                'icon' => $m->icon,
                'url' => $m->url,
                'route_name' => $m->route_name,
            ])
            ->values()
            ->all();

        $menu = $this->buildMenu($allModules, $permissions, $isSuperAdmin);

        return [
            'role' => $role ? [
                'id' => (int) $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'is_super_admin' => $isSuperAdmin,
            ] : null,
            'is_super_admin' => $isSuperAdmin,
            'permissions' => $permissions,
            'modules' => $modules,
            'menu' => $menu,
        ];
    }

    /**
     * Simpan snapshot di session aktif.
     */
    public function store(User $user, ?Session $session = null): void
    {
        $session ??= session();
        $session->put(self::SESSION_KEY, $this->build($user));
    }

    /**
     * Hapus snapshot (panggil saat logout).
     */
    public function forget(?Session $session = null): void
    {
        $session ??= session();
        $session->forget(self::SESSION_KEY);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(?Session $session = null): ?array
    {
        $session ??= session();

        return $session->get(self::SESSION_KEY);
    }

    /**
     * Cek permission dari snapshot. Falsy jika snapshot tidak ada (caller
     * boleh fallback ke DB).
     */
    public function hasPermission(string $action, int|string $module, ?Session $session = null): ?bool
    {
        $snapshot = $this->get($session);
        if ($snapshot === null) {
            return null;
        }

        if (! empty($snapshot['is_super_admin'])) {
            return true;
        }

        $permissions = $snapshot['permissions'] ?? [];

        if (is_string($module)) {
            $moduleEntry = collect($permissions)->first(fn (array $p) => ($p['name'] ?? null) === $module);
            if ($moduleEntry === null) {
                return false;
            }

            return (bool) ($moduleEntry[$action] ?? false);
        }

        $entry = $permissions[(int) $module] ?? null;
        if ($entry === null) {
            return false;
        }

        return (bool) ($entry[$action] ?? false);
    }

    /**
     * Resolve daftar module ID yang bisa diakses oleh role (read=true).
     *
     * @param  Collection<int, Module>  $allModules
     * @return list<int>
     */
    private function resolveAccessibleIds(?Role $role, Collection $allModules): array
    {
        if ($role === null) {
            return [];
        }

        if ($role->isSuperAdmin()) {
            return $allModules->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $list = (array) ($role->read ?? []);
        if (empty($list)) {
            return [];
        }

        if (in_array(Role::WILDCARD, $list, true)) {
            return $allModules->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return array_values(array_map('intval', $list));
    }

    /**
     * @param  Collection<int, Module>  $allModules
     * @return array<int, array<string, mixed>>
     */
    private function buildPermissions(?Role $role, Collection $allModules, bool $isSuperAdmin): array
    {
        $result = [];

        foreach ($allModules as $module) {
            $id = (int) $module->id;
            $entry = [
                'name' => $module->name,
                'read' => false,
                'create' => false,
                'update' => false,
                'delete' => false,
                'extra' => [],
            ];

            if ($isSuperAdmin) {
                $entry['read'] = $entry['create'] = $entry['update'] = $entry['delete'] = true;
            } elseif ($role !== null) {
                foreach (Role::ACTIONS as $action) {
                    $entry[$action] = $role->canAccess($action, $id);
                }
                $extra = (array) ($role->extra ?? []);
                $entry['extra'] = (array) ($extra[$module->name] ?? []);
            }

            $result[$id] = $entry;
        }

        return $result;
    }

    /**
     * Build menu (group → modules → children) berdasar permissions snapshot.
     *
     * @param  Collection<int, Module>  $allModules
     * @param  array<int, array<string, mixed>>  $permissions
     * @return array<int, array<string, mixed>>
     */
    private function buildMenu(Collection $allModules, array $permissions, bool $isSuperAdmin): array
    {
        $groups = ModuleGroup::query()->active()->orderBy('order')->get();
        $byParent = $allModules->groupBy('parent_id');

        $result = [];

        foreach ($groups as $group) {
            $roots = $allModules->where('module_group_id', $group->id)->where('parent_id', null);
            $filtered = $this->filterTree($roots, $byParent, $permissions, $isSuperAdmin);

            if ($filtered->isEmpty()) {
                continue;
            }

            $result[] = [
                'id' => (int) $group->id,
                'name' => $group->name,
                'label' => $group->label,
                'icon' => $group->icon,
                'modules' => $filtered->values()->all(),
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, Module>  $nodes
     * @param  Collection<int|string, Collection<int, Module>>  $byParent
     * @param  array<int, array<string, mixed>>  $permissions
     * @return Collection<int, array<string, mixed>>
     */
    private function filterTree(Collection $nodes, Collection $byParent, array $permissions, bool $isSuperAdmin): Collection
    {
        return $nodes
            ->map(function (Module $node) use ($byParent, $permissions, $isSuperAdmin): ?array {
                if ($node->isLeaf()) {
                    $canRead = $isSuperAdmin || ($permissions[(int) $node->id]['read'] ?? false);
                    if (! $canRead) {
                        return null;
                    }

                    return $this->nodeToArray($node, []);
                }

                $children = $byParent->get($node->id, collect());
                $filtered = $this->filterTree($children, $byParent, $permissions, $isSuperAdmin);

                if ($filtered->isEmpty()) {
                    return null;
                }

                return $this->nodeToArray($node, $filtered->all());
            })
            ->filter()
            ->values();
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    private function nodeToArray(Module $node, array $children): array
    {
        return [
            'id' => (int) $node->id,
            'name' => $node->name,
            'label' => $node->label,
            'icon' => $node->icon,
            'url' => $node->url,
            'route_name' => $node->route_name,
            'badge_source' => $node->badge_source,
            'external' => (bool) $node->external,
            'children' => $children,
        ];
    }
}
