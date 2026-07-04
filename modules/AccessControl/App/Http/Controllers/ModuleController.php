<?php

declare(strict_types=1);

namespace Modules\AccessControl\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Services\AdminLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\AccessControl\App\Http\Requests\StoreModuleGroupRequest;
use Modules\AccessControl\App\Http\Requests\StoreModuleRequest;
use Modules\AccessControl\App\Http\Requests\UpdateModuleGroupRequest;
use Modules\AccessControl\App\Http\Requests\UpdateModuleRequest;

class ModuleController extends Controller
{
    public function __construct(private readonly AdminLogService $logger) {}

    public function index(): Response
    {
        $groups = ModuleGroup::query()->orderBy('order')->get();
        $allModules = Module::query()->orderBy('order')->get();
        $byParent = $allModules->groupBy('parent_id');

        // Hitung berapa role yang punya read access per module ID (untuk badge "X role")
        $rolesCountPerModule = $this->countRolesPerModule();

        $tree = $groups->map(function (ModuleGroup $g) use ($allModules, $byParent, $rolesCountPerModule) {
            $roots = $allModules->where('module_group_id', $g->id)->where('parent_id', null);

            return [
                'id' => $g->id,
                'name' => $g->name,
                'label' => $g->label,
                'icon' => $g->icon,
                'order' => $g->order,
                'active' => (bool) $g->active,
                'modules' => $roots
                    ->map(fn (Module $m) => $this->mapNode($m, $byParent, $rolesCountPerModule))
                    ->values()
                    ->all(),
            ];
        })->all();

        return Inertia::render('access-control::Module/index', [
            'tree' => $tree,
            'groups' => $groups,
            'modules' => $allModules,
        ]);
    }

    /**
     * Mapping module_id => jumlah role yang punya read permission ke modul tsb.
     * Hitung dari kolom jsonb roles.read; "*" dianggap berlaku ke semua.
     *
     * @return array<int, int>
     */
    private function countRolesPerModule(): array
    {
        $roles = Role::query()
            ->where('is_active', true)
            ->get(['id', 'read']);

        $counts = [];
        $allCount = 0;

        foreach ($roles as $r) {
            $reads = (array) ($r->read ?? []);
            if (in_array('*', $reads, true)) {
                $allCount++;

                continue;
            }
            foreach ($reads as $id) {
                $key = (int) $id;
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        // Tambahkan wildcard ke semua module ID yang ada
        if ($allCount > 0) {
            foreach (Module::query()->pluck('id') as $id) {
                $counts[(int) $id] = ($counts[(int) $id] ?? 0) + $allCount;
            }
        }

        return $counts;
    }

    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $module = Module::query()->create($data);
        $this->logger->log('created', "Membuat modul {$module->name}", $module, [], $data, 'module-management');

        return redirect()->route('modules.index')->with('success', 'Modul berhasil dibuat.');
    }

    public function update(UpdateModuleRequest $request, Module $module): RedirectResponse
    {
        $data = $request->validated();
        $old = $module->only(array_keys($data));
        $module->update($data);
        $this->logger->log('updated', "Mengubah modul {$module->name}", $module, $old, $data, 'module-management');

        return redirect()->route('modules.index')->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroy(Module $module): RedirectResponse
    {
        $module->delete();
        $this->logger->log('deleted', "Menghapus modul {$module->name}", $module, [], [], 'module-management');

        return back()->with('success', 'Modul berhasil dihapus.');
    }

    /**
     * Toggle aktif/nonaktif inline tanpa modal.
     */
    public function toggleActive(Module $module): RedirectResponse
    {
        abort_unless(
            request()->user()?->hasPermission('update', 'module-management'),
            403,
        );
        $module->update(['active' => ! $module->active]);
        $this->logger->log(
            action: 'updated',
            description: "Status modul {$module->name} → ".($module->active ? 'aktif' : 'nonaktif'),
            model: $module,
            old: ['active' => ! $module->active],
            new: ['active' => $module->active],
            module: 'module-management',
        );

        return back()->with('success', 'Status modul diperbarui.');
    }

    /**
     * Bulk reorder + reparent dari drag-and-drop UI.
     *
     * Body: { items: [ { id, parent_id|null, module_group_id|null, order } ] }
     *
     * Validasi:
     *   - Tidak boleh circular (parent != self, parent != descendant)
     *   - Depth maks 2 di bawah root (root → child → grandchild)
     *   - Leaf rule (url+route harus dua-duanya ada atau dua-duanya kosong) tidak berubah,
     *     hanya posisi tree yang diubah
     */
    public function reorder(Request $request): JsonResponse
    {
        abort_unless(
            request()->user()?->hasPermission('update', 'module-management'),
            403,
        );

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:modules,id'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:modules,id'],
            'items.*.module_group_id' => ['nullable', 'integer', 'exists:module_groups,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        // Cek depth max 2 dari root + no circular
        $byId = collect($data['items'])->keyBy('id');

        foreach ($data['items'] as $item) {
            $id = $item['id'];
            $depth = 0;
            $parent = $item['parent_id'];
            $visited = [$id];
            while ($parent) {
                if (in_array($parent, $visited, true)) {
                    return response()->json(['message' => 'Circular reference terdeteksi.'], 422);
                }
                $visited[] = $parent;
                $depth++;
                if ($depth > 2) {
                    return response()->json(['message' => 'Kedalaman maksimum 2 di bawah root.'], 422);
                }
                $next = $byId->get($parent);
                $parent = $next ? $next['parent_id'] : null;
            }
        }

        // Apply: kalau parent ada, module_group_id harus null (group hanya di root)
        DB::transaction(function () use ($data): void {
            foreach ($data['items'] as $item) {
                Module::query()->where('id', $item['id'])->update([
                    'parent_id' => $item['parent_id'],
                    'module_group_id' => $item['parent_id'] ? null : $item['module_group_id'],
                    'order' => $item['order'],
                ]);
            }
        });

        $this->logger->log(
            action: 'updated',
            description: 'Reorder modul via drag-and-drop ('.count($data['items']).' modul)',
            module: 'module-management',
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Geser order modul ke atas/bawah di antara sibling-nya
     * (sesama parent_id / sesama module_group_id).
     */
    public function move(Module $module, string $direction): RedirectResponse
    {
        abort_unless(
            request()->user()?->hasPermission('update', 'module-management'),
            403,
        );

        if (! in_array($direction, ['up', 'down'], true)) {
            return back()->with('error', 'Arah tidak valid.');
        }

        $siblings = Module::query()
            ->where('parent_id', $module->parent_id)
            ->when(
                $module->parent_id === null,
                fn ($q) => $q->where('module_group_id', $module->module_group_id),
            )
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $idx = $siblings->search(fn ($m) => $m->id === $module->id);
        if ($idx === false) {
            return back();
        }

        $target = $direction === 'up' ? $idx - 1 : $idx + 1;
        if ($target < 0 || $target >= $siblings->count()) {
            return back()->with('warning', 'Sudah di posisi paling '.($direction === 'up' ? 'atas' : 'bawah').'.');
        }

        // Tukar dan resequence (0, 1, 2, ...)
        $reordered = $siblings->toArray();
        [$reordered[$idx], $reordered[$target]] = [$reordered[$target], $reordered[$idx]];

        foreach ($reordered as $i => $row) {
            Module::query()->where('id', $row['id'])->update(['order' => $i]);
        }

        return back()->with('success', 'Urutan modul diperbarui.');
    }

    public function storeGroup(StoreModuleGroupRequest $request): RedirectResponse
    {
        ModuleGroup::query()->create($request->validated());

        return back()->with('success', 'Grup berhasil ditambahkan.');
    }

    public function updateGroup(UpdateModuleGroupRequest $request, ModuleGroup $group): RedirectResponse
    {
        $group->update($request->validated());

        return back()->with('success', 'Grup berhasil diperbarui.');
    }

    public function destroyGroup(ModuleGroup $group): RedirectResponse
    {
        if ($group->modules()->exists()) {
            return back()->with('error', 'Grup masih memiliki modul.');
        }
        $group->delete();

        return back()->with('success', 'Grup berhasil dihapus.');
    }

    /**
     * @param  Collection<int|string, \Illuminate\Database\Eloquent\Collection<int, Module>>  $byParent
     * @param  array<int, int>  $rolesCount
     * @return array<string, mixed>
     */
    private function mapNode(Module $m, $byParent, array $rolesCount = []): array
    {
        $children = $byParent->get($m->id, collect());

        return [
            'id' => $m->id,
            'name' => $m->name,
            'label' => $m->label,
            'icon' => $m->icon,
            'url' => $m->url,
            'route_name' => $m->route_name,
            'order' => $m->order,
            'active' => (bool) $m->active,
            'is_leaf' => $m->isLeaf(),
            'roles_count' => $rolesCount[(int) $m->id] ?? 0,
            'children' => $children
                ->map(fn (Module $c) => $this->mapNode($c, $byParent, $rolesCount))
                ->values()
                ->all(),
        ];
    }
}
