<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->last_login_at === null) {
            $user->forceFill(['last_login_at' => Carbon::now()])->saveQuietly();
        }

        return $next($request);
    }
}
