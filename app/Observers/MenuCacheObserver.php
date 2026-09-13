<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Support\MenuCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Flush cache `menu.role.{id}` setiap kali ada perubahan pada
 * Module / ModuleGroup / Role. Tidak pakai cache tags supaya store menu
 * tetap bisa dipindah ke driver non-tag (mis. `array` saat testing).
 */
class MenuCacheObserver
{
    public function saved(Model $model): void
    {
        $this->flush();
    }

    public function deleted(Model $model): void
    {
        $this->flush();
    }

    public function restored(Model $model): void
    {
        $this->flush();
    }

    public function forceDeleted(Model $model): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        // Flush per-role menu cache. Tanpa tags, kita iterate role IDs.
        Role::query()->pluck('id')->each(function ($roleId): void {
            MenuCache::forgetRole($roleId);
        });
    }

    public static function bootstrap(): void
    {
        Module::observe(self::class);
        ModuleGroup::observe(self::class);
        Role::observe(self::class);
    }
}
