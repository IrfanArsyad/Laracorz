<?php

declare(strict_types=1);

namespace Modules\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Auth\App\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly UserSessionService $userSession) {}

    public function create(): Response
    {
        return Inertia::render('auth::login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        if ($user) {
            $user->forceFill(['last_login_at' => now()])->save();
            $this->userSession->store($user, $request->session());
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->userSession->forget($request->session());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
