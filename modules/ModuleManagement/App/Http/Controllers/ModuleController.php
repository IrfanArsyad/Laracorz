<?php

declare(strict_types=1);

namespace Modules\ModuleManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleGroup;
use App\Services\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ModuleManagement\App\Http\Requests\StoreModuleGroupRequest;
use Modules\ModuleManagement\App\Http\Requests\StoreModuleRequest;
use Modules\ModuleManagement\App\Http\Requests\UpdateModuleGroupRequest;
use Modules\ModuleManagement\App\Http\Requests\UpdateModuleRequest;

class ModuleController extends Controller
{
    public function __construct(private readonly AdminLogService $logger) {}

    public function index(): Response
    {
        $groups = ModuleGroup::query()->orderBy('order')->get();
        $allModules = Module::query()->orderBy('order')->get();
        $byParent = $allModules->groupBy('parent_id');

        $tree = $groups->map(function (ModuleGroup $g) use ($allModules, $byParent) {
            $roots = $allModules->where('module_group_id', $g->id)->where('parent_id', null);

            return [
                'id' => $g->id,
                'name' => $g->name,
                'label' => $g->label,
                'icon' => $g->icon,
                'order' => $g->order,
                'active' => (bool) $g->active,
                'modules' => $roots
                    ->map(fn (Module $m) => $this->mapNode($m, $byParent))
                    ->values()
                    ->all(),
            ];
        })->all();

        return Inertia::render('module-management::index', [
            'tree' => $tree,
            'groups' => $groups,
            'modules' => $allModules,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('module-management::create', [
            'groups' => ModuleGroup::query()->orderBy('order')->get(),
            'modules' => Module::query()->orderBy('order')->get(),
        ]);
    }

    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $module = Module::query()->create($data);
        $this->logger->log('created', "Membuat modul {$module->name}", $module, [], $data, 'module-management');

        return redirect()->route('modules.index')->with('success', 'Modul berhasil dibuat.');
    }

    public function edit(Module $module): Response
    {
        return Inertia::render('module-management::edit', [
            'module' => $module,
            'groups' => ModuleGroup::query()->orderBy('order')->get(),
            'modules' => Module::query()->where('id', '!=', $module->id)->orderBy('order')->get(),
        ]);
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
     * @param  \Illuminate\Support\Collection<int|string, \Illuminate\Database\Eloquent\Collection<int, Module>>  $byParent
     * @return array<string, mixed>
     */
    private function mapNode(Module $m, $byParent): array
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
            'children' => $children
                ->map(fn (Module $c) => $this->mapNode($c, $byParent))
                ->values()
                ->all(),
        ];
    }
}
