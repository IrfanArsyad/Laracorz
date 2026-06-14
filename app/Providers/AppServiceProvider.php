<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Carbon;
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
    }
}
