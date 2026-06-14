<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\AuthEventListener;
use App\Listeners\JobFailedListener;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobFailed;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        JobFailed::class => [JobFailedListener::class],
    ];

    protected $subscribe = [
        AuthEventListener::class,
    ];

    public function boot(): void {}
}
