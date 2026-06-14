<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminLogService;
use App\Services\FileService;
use App\Support\SearchFilterDto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminLogService $logger,
        private readonly FileService $files,
    ) {}

    public function index(Request $request): Response
    {
        $dto = SearchFilterDto::fromRequest($request);
        $trashed = (bool) $request->boolean('trashed');

        $query = User::query()
            ->with('role')
            ->when($trashed, fn ($q) => $q->onlyTrashed())
            ->search($dto->search)
            ->sort($dto->sort ?? 'created_at', $dto->direction ?? 'desc');

        $filters = $dto->filters;
        if (! empty($filters['role_id'])) {
            $query->where('role_id', (int) $filters['role_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $data = $query->paginate($dto->perPage)->withQueryString();

        return Inertia::render('user-management::index', [
            'data' => $data,
            'roles' => Role::query()->orderBy('display_name')->get(['id', 'name', 'display_name']),
            'filters' => array_merge(['search' => $dto->search, 'sort' => $dto->sort, 'direction' => $dto->direction], (array) $filters),
            'trashed' => $trashed,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('user-management::create', [
            'roles' => Role::query()->where('is_active', true)->orderBy('display_name')->get(['id', 'name', 'display_name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_BANNED])],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->files->upload($request->file('avatar'), 'avatars', null, 512);
        }
        $data['password'] = Hash::make($data['password']);
        $user = User::query()->create($data);

        $this->logger->log('created', "Membuat user {$user->email}", $user, [], $user->only(['name', 'username', 'email', 'role_id', 'status']), 'user-management');

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user): Response
    {
        return Inertia::render('user-management::show', [
            'user' => $user->load('role'),
            'logs' => AdminLog::query()
                ->where('loggable_type', User::class)
                ->where('loggable_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('user-management::edit', [
            'user' => $user->load('role'),
            'roles' => Role::query()->where('is_active', true)->orderBy('display_name')->get(['id', 'name', 'display_name']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_BANNED])],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $old = $user->only(['name', 'username', 'email', 'role_id', 'status']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->files->upload($request->file('avatar'), 'avatars', $user->avatar, 512);
        }
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);

        $this->logger->log('updated', "Mengubah user {$user->email}", $user, $old, $user->only(['name', 'username', 'email', 'role_id', 'status']), 'user-management');

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        $this->logger->log('deleted', "Menghapus user {$user->email}", $user, [], [], 'user-management');

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::query()->onlyTrashed()->findOrFail($id);
        $user->restore();
        $this->logger->log('restored', "Memulihkan user {$user->email}", $user, [], [], 'user-management');

        return back()->with('success', 'Pengguna berhasil dipulihkan.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $dto = SearchFilterDto::fromRequest($request);
        $rows = User::query()->with('role')->search($dto->search)->orderByDesc('created_at')->cursor();

        $this->logger->log('export', 'Ekspor data pengguna', null, [], [], 'user-management');

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'Email', 'Role', 'Status', 'Dibuat']);
            foreach ($rows as $u) {
                fputcsv($out, [
                    $u->name,
                    $u->email,
                    $u->role?->display_name ?? '-',
                    $u->status,
                    $u->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, 'users-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
