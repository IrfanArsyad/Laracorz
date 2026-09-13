<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Role;
use App\Support\MenuCache;

class RoleObserver
{
    public function saved(Role $role): void
    {
        MenuCache::forgetRole($role->id);
    }

    public function deleted(Role $role): void
    {
        MenuCache::forgetRole($role->id);
    }
}
