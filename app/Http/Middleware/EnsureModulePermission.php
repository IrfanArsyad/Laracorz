<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureModulePermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'read'): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->hasPermission($action, $module)) {
            throw new AccessDeniedHttpException("Anda tidak memiliki izin untuk {$action} pada modul {$module}.");
        }

        return $next($request);
    }
}
