<?php

declare(strict_types=1);

use App\Exceptions\BusinessException;
use App\Http\Middleware\EnsureModulePermission;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackLastActivity;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            TrackLastActivity::class,
        ]);

        $middleware->alias([
            'module.permission' => EnsureModulePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (BusinessException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->header('X-Inertia') && ! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $e instanceof AuthenticationException => 401,
                $e instanceof NotFoundHttpException => 404,
                $e instanceof TokenMismatchException => 419,
                $e instanceof TooManyRequestsHttpException => 429,
                $e instanceof HttpException => $e->getStatusCode(),
                default => 500,
            };

            if ($status === 401) {
                return null;
            }

            if (! in_array($status, [403, 404, 419, 500, 503, 429], true)) {
                return null;
            }

            return Inertia::render("errors/{$status}", [
                'status' => $status,
                'message' => $e->getMessage() ?: null,
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
