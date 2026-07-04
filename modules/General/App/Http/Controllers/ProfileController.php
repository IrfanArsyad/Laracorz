<?php

declare(strict_types=1);

namespace Modules\General\App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Services\FileService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(private readonly FileService $files) {}

    public function edit(Request $request): Response
    {
        return Inertia::render('general::Profile/edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'sessions' => $this->sessions($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();
        $path = $this->files->upload(
            file: $request->file('avatar'),
            directory: 'avatars',
            oldFile: $user->avatar,
            resizeMax: 512,
        );
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function logoutOtherSessions(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        Auth::logoutOtherDevices($request->input('password'));

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('success', 'Sesi lain berhasil dihentikan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sessions(Request $request): array
    {
        return DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn ($row) => [
                'id' => $row->id,
                'ip_address' => $row->ip_address,
                'user_agent' => $row->user_agent,
                'last_activity' => $row->last_activity,
                'current' => $row->id === $request->session()->getId(),
            ])
            ->all();
    }
}
