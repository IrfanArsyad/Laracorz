<?php

declare(strict_types=1);

namespace Modules\UserManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminLogService;
use App\Services\FileService;
use App\Support\SearchFilterDto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Modules\UserManagement\App\Http\Requests\StoreUserRequest;
use Modules\UserManagement\App\Http\Requests\UpdateUserRequest;
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

        return Inertia::render('user-management::index', [
            'data' => $query->paginate($dto->perPage)->withQueryString(),
            'roles' => Role::query()->orderBy('display_name')->get(['id', 'name', 'display_name']),
            'filters' => array_merge(
                ['search' => $dto->search, 'sort' => $dto->sort, 'direction' => $dto->direction],
                (array) $filters,
            ),
            'trashed' => $trashed,
            'stats' => Inertia::defer(fn (): array => [
                'total' => User::query()->count(),
                'active' => User::query()->where('status', User::STATUS_ACTIVE)->count(),
                'inactive' => User::query()->where('status', User::STATUS_INACTIVE)->count(),
                'banned' => User::query()->where('status', User::STATUS_BANNED)->count(),
            ]),
        ]);
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

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->files->upload($request->file('avatar'), 'avatars', null, 512);
        }
        $data['password'] = Hash::make($data['password']);

        $user = User::query()->create($data);

        $this->logger->log(
            action: 'created',
            description: "Membuat user {$user->email}",
            model: $user,
            new: $user->only(['name', 'username', 'email', 'role_id', 'status']),
            module: 'user-management',
        );

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
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

        $this->logger->log(
            action: 'updated',
            description: "Mengubah user {$user->email}",
            model: $user,
            old: $old,
            new: $user->only(['name', 'username', 'email', 'role_id', 'status']),
            module: 'user-management',
        );

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
            fputcsv($out, ['Nama', 'Username', 'Email', 'Role', 'Status', 'Dibuat']);
            foreach ($rows as $u) {
                fputcsv($out, [
                    $u->name,
                    $u->username,
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
