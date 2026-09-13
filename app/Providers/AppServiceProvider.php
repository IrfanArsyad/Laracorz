<?php

declare(strict_types=1);

namespace App\Providers;

use App\Observers\MenuCacheObserver;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard(false);

        Password::defaults(function () {
            $rule = Password::min(8)
                ->mixedCase()
                ->numbers();

            return $this->app->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Carbon::setLocale('id');

        /*
         * Serialize semua Carbon (timestamps di JSON / Inertia response) ke format
         *   Y-m-d H:i:s
         * supaya frontend dapat string yang konsisten dan rapi tampilkan,
         * bukan ISO 8601 dengan microsecond + Z.
         *
         * Kalau perlu ISO original di endpoint tertentu, panggil:
         *   $date->toIso8601String()
         */
        Carbon::serializeUsing(fn (CarbonInterface $date) => $date->format('Y-m-d H:i:s'));

        MenuCacheObserver::bootstrap();

        $this->loadModuleMigrations();
    }

    /**
     * Daftarkan migration tiap modul: modules/{Module}/Database/Migrations.
     *
     * Modul tidak pakai ServiceProvider sendiri — registrasinya cukup tiga:
     * glob route di routes/web.php, PSR-4 `Modules\` di composer.json, dan
     * glob ini. Tambah modul = tambah folder.
     */
    private function loadModuleMigrations(): void
    {
        $paths = glob(base_path('modules/*/Database/Migrations'), GLOB_ONLYDIR) ?: [];

        if ($paths !== []) {
            $this->loadMigrationsFrom($paths);
        }
    }
}
