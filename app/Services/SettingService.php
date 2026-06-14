<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const string CACHE_KEY = 'settings.all';

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => Setting::query()->get()
                ->mapWithKeys(fn (Setting $s) => [
                    $s->key => $this->unwrap($s->value),
                ])
                ->all(),
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->all(), $key, $default);
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type],
        );

        $this->flush();

        return $setting;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function unwrap(mixed $value): mixed
    {
        if (is_array($value) && array_keys($value) === [0] && count($value) === 1) {
            return $value[0];
        }

        return $value;
    }
}
