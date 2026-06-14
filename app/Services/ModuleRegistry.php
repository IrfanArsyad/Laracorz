<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Cache;

class ModuleRegistry
{
    private const string CACHE_KEY = 'modules.map';

    /**
     * @return array<string, int>
     */
    public function map(): array
    {
        return Cache::rememberForever(
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
        Cache::forget(self::CACHE_KEY);
    }
}
