<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Module;
use App\Models\Role;
use App\Services\ModuleRegistry;
use Illuminate\Support\Facades\Cache;

class ModuleObserver
{
    public function __construct(private readonly ModuleRegistry $registry) {}

    public function saved(Module $module): void
    {
        if ($module->isDirty('name') && $module->getOriginal('name')) {
            $oldName = $module->getOriginal('name');
            $newName = $module->name;
            $this->renameInRolesExtra($oldName, $newName);
        }

        $this->flushAll();
    }

    public function deleted(Module $module): void
    {
        $this->stripIdFromRoles((int) $module->id);
        $this->flushAll();
    }

    public function forceDeleted(Module $module): void
    {
        $this->stripIdFromRoles((int) $module->id);
        $this->flushAll();
    }

    public function restored(Module $module): void
    {
        $this->flushAll();
    }

    private function stripIdFromRoles(int $moduleId): void
    {
        foreach (Role::query()->withTrashed()->cursor() as $role) {
            $dirty = false;
            foreach (Role::ACTIONS as $action) {
                $list = (array) ($role->{$action} ?? []);
                if (! $list) {
                    continue;
                }
                $filtered = array_values(array_filter($list, fn ($v) => (string) $v !== (string) $moduleId));
                if (count($filtered) !== count($list)) {
                    $role->{$action} = $filtered;
                    $dirty = true;
                }
            }
            if ($dirty) {
                $role->saveQuietly();
            }
        }
    }

    private function renameInRolesExtra(string $oldName, string $newName): void
    {
        foreach (Role::query()->withTrashed()->cursor() as $role) {
            $extra = (array) ($role->extra ?? []);
            if (array_key_exists($oldName, $extra)) {
                $extra[$newName] = $extra[$oldName];
                unset($extra[$oldName]);
                $role->extra = $extra;
                $role->saveQuietly();
            }
        }
    }

    private function flushAll(): void
    {
        $this->registry->flush();
        Cache::flush();
    }
}
