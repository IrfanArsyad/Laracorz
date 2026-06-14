<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class RoleObserver
{
    public function saved(Role $role): void
    {
        Cache::forget("menu.role.{$role->id}");
    }

    public function deleted(Role $role): void
    {
        Cache::forget("menu.role.{$role->id}");
    }
}
