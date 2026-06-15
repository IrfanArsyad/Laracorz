<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Flush cache `menu.role.{id}` dan `modules.summary` setiap kali ada
 * perubahan pada Module / ModuleGroup. Tidak pakai cache tags supaya
 * kompatibel dengan driver `database` / `file`.
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
        Cache::forget('modules.summary');

        // Flush per-role menu cache. Tanpa tags, kita iterate role IDs.
        Role::query()->pluck('id')->each(function ($roleId): void {
            Cache::forget("menu.role.{$roleId}");
        });
    }

    public static function bootstrap(): void
    {
        Module::observe(self::class);
        ModuleGroup::observe(self::class);
        Role::observe(self::class);
    }
}
