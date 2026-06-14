<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

abstract class ModulePolicy
{
    abstract protected function moduleName(): string;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('read', $this->moduleName());
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('read', $this->moduleName());
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create', $this->moduleName());
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update', $this->moduleName());
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete', $this->moduleName());
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('update', $this->moduleName());
    }

    public function extra(User $user, string $action): bool
    {
        return $user->canExtra($this->moduleName(), $action);
    }
}
