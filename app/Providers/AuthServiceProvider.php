<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Observers\ModuleGroupObserver;
use App\Observers\ModuleObserver;
use App\Observers\RoleObserver;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Module::observe(ModuleObserver::class);
        ModuleGroup::observe(ModuleGroupObserver::class);
        Role::observe(RoleObserver::class);
    }
}
