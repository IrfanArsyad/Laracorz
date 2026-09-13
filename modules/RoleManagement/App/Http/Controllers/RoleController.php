<?php

declare(strict_types=1);

namespace Modules\RoleManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Services\AdminLogService;
use App\Support\SearchFilterDto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Modules\RoleManagement\App\Http\Requests\StoreRoleRequest;
use Modules\RoleManagement\App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function __construct(private readonly AdminLogService $logger) {}

    public function index(Request $request): Response
    {
        $dto = SearchFilterDto::fromRequest($request);

        $roles = Role::query()
            ->search($dto->search)
            ->sort($dto->sort, $dto->direction)
            ->withCount('users')
            ->paginate($dto->perPage)
            ->withQueryString();

        return Inertia::render('role-management::index', [
            'data' => $roles,
            'filters' => [
                'search' => $dto->search,
                'sort' => $dto->sort,
                'direction' => $dto->direction,
            ],
            // matrix dipakai modal Create/Edit di index page (deferred biar load cepat)
            'matrix' => Inertia::defer(fn () => $this->matrix()),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::query()->create($data);
        $this->logger->log('created', "Membuat role {$role->name}", $role, [], $data, 'role-management');

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        $old = $role->only(['name', 'display_name', 'is_active', 'read', 'create', 'update', 'delete', 'extra']);
        $role->update($data);
        $this->logger->log('updated', "Mengubah role {$role->name}", $role, $old, $data, 'role-management');

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->name === Role::SUPER_ADMIN, 403, 'Role super-admin tidak dapat dihapus.');
        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih digunakan pengguna lain.');
        }

        $role->delete();
        $this->logger->log('deleted', "Menghapus role {$role->name}", $role, [], [], 'role-management');

        return back()->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Bangun struktur grup → root modules → child modules untuk matrix permission.
     *
     * Tree dibangun manual dari flat collection (grouped by parent_id) supaya
     * tidak memicu lazy-load children pada model.
     *
     * @return array<int, array<string, mixed>>
     */
    private function matrix(): array
    {
        $groups = ModuleGroup::query()->active()->orderBy('order')->get();
        $allModules = Module::query()->active()->orderBy('order')->get();
        $byParent = $allModules->groupBy('parent_id');

        return $groups->map(function (ModuleGroup $g) use ($allModules, $byParent) {
            $roots = $allModules->where('module_group_id', $g->id)->where('parent_id', null);

            return [
                'id' => $g->id,
                'name' => $g->name,
                'label' => $g->label,
                'modules' => $roots
                    ->map(fn (Module $m) => $this->buildNode($m, $byParent))
                    ->values()
                    ->all(),
            ];
        })->all();
    }

    /**
     * @param  Collection<int|string, \Illuminate\Database\Eloquent\Collection<int, Module>>  $byParent
     * @return array<string, mixed>
     */
    private function buildNode(Module $m, $byParent): array
    {
        $children = $byParent->get($m->id, collect());

        return [
            'id' => $m->id,
            'name' => $m->name,
            'label' => $m->label,
            'is_leaf' => $m->isLeaf(),
            'extra_actions' => $m->extra_actions ?? [],
            'children' => $children
                ->map(fn (Module $c) => $this->buildNode($c, $byParent))
                ->values()
                ->all(),
        ];
    }
}
