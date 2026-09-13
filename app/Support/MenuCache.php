<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Gerbang tunggal ke cache store menu & permission (Redis, index terpisah).
 *
 * Dipisah dari cache aplikasi karena invalidasi menu terjadi tiap kali module
 * tree berubah. Sebelumnya observer memanggil Cache::flush() global sehingga
 * cache setting ikut terbuang; sekarang flush-nya terbatas pada store ini.
 */
final class MenuCache
{
    public const string MODULE_MAP_KEY = 'modules.map';

    public static function store(): Repository
    {
        return Cache::store(config('cache.menu_store', 'menu'));
    }

    public static function roleKey(int|string $roleId): string
    {
        return "menu.role.{$roleId}";
    }

    public static function forgetRole(int|string $roleId): void
    {
        self::store()->forget(self::roleKey($roleId));
    }

    /**
     * Buang seluruh isi store menu. Aman dipanggil dari observer karena
     * store ini hanya berisi menu per-role dan module map.
     */
    public static function flush(): void
    {
        // clear() dari PSR-16; flush() tidak ada di kontrak Repository.
        self::store()->clear();
    }
}
