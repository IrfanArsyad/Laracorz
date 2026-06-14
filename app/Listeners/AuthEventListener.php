<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\AdminLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class AuthEventListener
{
    public function __construct(private readonly AdminLogService $logger) {}

    public function onLogin(Login $event): void
    {
        $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
        $this->logger->log('login', "Login user {$event->user->email}", $event->user, [], [], 'auth');
    }

    public function onLogout(Logout $event): void
    {
        if ($event->user) {
            $this->logger->log('logout', "Logout user {$event->user->email}", $event->user, [], [], 'auth');
        }
    }

    public function onFailed(Failed $event): void
    {
        $this->logger->log('login_failed', 'Login gagal: '.($event->credentials['email'] ?? 'unknown'), null, [], [], 'auth');
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, [self::class, 'onLogin']);
        $events->listen(Logout::class, [self::class, 'onLogout']);
        $events->listen(Failed::class, [self::class, 'onFailed']);
    }
}
