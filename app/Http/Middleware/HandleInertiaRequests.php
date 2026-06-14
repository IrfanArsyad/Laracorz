<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\AuthUserResource;
use App\Models\Module;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user()
                    ? AuthUserResource::make($request->user()->load('role'))
                    : null,
                'permissions' => fn () => $request->user()?->role
                    ? collect($request->user()->role->only(['read', 'create', 'update', 'delete']))
                        ->map(fn ($v) => $v ?? [])
                        ->all()
                    : null,
            ],
            'menu' => fn () => app(MenuService::class)->forUser($request->user()),
            'modules' => fn () => Cache::rememberForever(
                'modules.summary',
                fn () => Module::query()->withoutTrashed()->get(['id', 'name'])->all(),
            ),
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
            'app' => [
                'name' => config('app.name'),
                'locale' => app()->getLocale(),
                'fallback_locale' => config('app.fallback_locale'),
            ],
            'ziggy' => fn (): array => array_merge((new \Tighten\Ziggy\Ziggy)->toArray(), [
                'location' => $request->url(),
            ]),
        ];
    }
}
