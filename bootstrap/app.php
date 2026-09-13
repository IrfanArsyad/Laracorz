<?php

declare(strict_types=1);

use App\Exceptions\BusinessException;
use App\Http\Middleware\EnsureModulePermission;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackLastActivity;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
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

            /*
             * Exception di bawah ini sudah punya penanganan bawaan Laravel:
             * validasi di-redirect balik lengkap dengan error per-field, auth
             * di-redirect ke login, HttpResponseException membawa response-nya
             * sendiri. Handler ini jalan LEBIH DULU daripada penanganan bawaan
             * itu (lihat Foundation\Exceptions\Handler::render — renderViaCallbacks
             * dipanggil sebelum match ValidationException), jadi tanpa guard ini
             * semuanya jatuh ke `default => 500` dan berubah jadi halaman error.
             */
            if (
                $e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof HttpResponseException
            ) {
                return null;
            }

            $status = match (true) {
                $e instanceof NotFoundHttpException => 404,
                $e instanceof TokenMismatchException => 419,
                $e instanceof TooManyRequestsHttpException => 429,
                $e instanceof HttpException => $e->getStatusCode(),
                default => 500,
            };

            if (! in_array($status, [403, 404, 419, 500, 503, 429], true)) {
                return null;
            }

            return Inertia::render("errors/{$status}", [
                'status' => $status,
                'message' => $e->getMessage() ?: null,
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
