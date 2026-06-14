<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Services\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validate($request);
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

    public function update(Request $request, Module $module): RedirectResponse
    {
        $data = $this->validate($request, $module);
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

    public function storeGroup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:module_groups,name'],
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'order' => ['integer', 'min:0'],
            'active' => ['boolean'],
        ]);
        ModuleGroup::query()->create($data);

        return back()->with('success', 'Grup berhasil ditambahkan.');
    }

    public function updateGroup(Request $request, ModuleGroup $group): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('module_groups', 'name')->ignore($group->id)],
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'order' => ['integer', 'min:0'],
            'active' => ['boolean'],
        ]);
        $group->update($data);

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
     * @return array<string, mixed>
     */
    private function validate(Request $request, ?Module $module = null): array
    {
        $rules = [
            'parent_id' => ['nullable', 'integer', 'exists:modules,id'],
            'module_group_id' => ['nullable', 'integer', 'exists:module_groups,id'],
            'name' => ['required', 'string', 'max:80', Rule::unique('modules', 'name')->ignore($module?->id)],
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'url' => ['nullable', 'string', 'max:200'],
            'route_name' => ['nullable', 'string', 'max:200', Rule::unique('modules', 'route_name')->ignore($module?->id)],
            'badge_source' => ['nullable', 'string', 'max:80'],
            'extra_actions' => ['nullable', 'array'],
            'active' => ['boolean'],
            'external' => ['boolean'],
            'order' => ['integer', 'min:0'],
        ];
        $data = $request->validate($rules);

        // Tree integrity: group only at root
        if ($data['parent_id'] ?? null) {
            $data['module_group_id'] = null;
        } elseif (! ($data['module_group_id'] ?? null)) {
            abort(422, 'Modul root wajib memiliki grup.');
        }

        // Leaf wajib url+route_name; container tidak boleh
        $hasUrl = ! empty($data['url']);
        $hasRoute = ! empty($data['route_name']);
        if ($hasUrl !== $hasRoute) {
            abort(422, 'Leaf wajib mengisi url dan route_name. Container kosongkan keduanya.');
        }

        return $data;
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
