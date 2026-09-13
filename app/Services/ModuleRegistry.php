<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Support\MenuCache;

class ModuleRegistry
{
    private const string CACHE_KEY = MenuCache::MODULE_MAP_KEY;

    /**
     * @return array<string, int>
     */
    public function map(): array
    {
        return MenuCache::store()->rememberForever(
            self::CACHE_KEY,
            fn (): array => Module::query()
                ->withoutTrashed()
                ->pluck('id', 'name')
                ->map(fn ($id) => (int) $id)
                ->all(),
        );
    }

    public function resolveId(string $name): ?int
    {
        return $this->map()[$name] ?? null;
    }

    public function resolveName(int $id): ?string
    {
        return array_search($id, $this->map(), true) ?: null;
    }

    public function flush(): void
    {
        MenuCache::store()->forget(self::CACHE_KEY);
    }
}
