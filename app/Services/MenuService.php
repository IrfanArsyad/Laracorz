<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
use App\Services\UserSessionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function forUser(?User $user): array
    {
        if (! $user || ! $user->role) {
            return [];
        }

        // Snapshot session menang: menu sudah dipersonalisasi & locked saat login.
        if (app()->bound('session.store') && session()->has(UserSessionService::SESSION_KEY)) {
            $snapshot = app(UserSessionService::class)->get();
            if ($snapshot !== null) {
                return $snapshot['menu'] ?? [];
            }
        }

        $cacheKey = "menu.role.{$user->role_id}";

        return Cache::rememberForever($cacheKey, fn (): array => $this->build($user));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function build(User $user): array
    {
        $groups = ModuleGroup::query()
            ->active()
            ->orderBy('order')
            ->get();

        $allModules = Module::query()
            ->active()
            ->orderBy('order')
            ->get();

        $byParent = $allModules->groupBy('parent_id');

        $result = [];

        foreach ($groups as $group) {
            $roots = $allModules->where('module_group_id', $group->id)->where('parent_id', null);
            $filtered = $this->filterTree($roots, $byParent, $user);

            if ($filtered->isEmpty()) {
                continue;
            }

            $result[] = [
                'id' => $group->id,
                'name' => $group->name,
                'label' => $group->label,
                'icon' => $group->icon,
                'modules' => $filtered->values()->all(),
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, Module>  $nodes
     * @param  Collection<int|string, Collection<int, Module>>  $byParent
     * @return Collection<int, array<string, mixed>>
     */
    private function filterTree(Collection $nodes, Collection $byParent, User $user): Collection
    {
        return $nodes
            ->map(fn (Module $node): ?array => $this->mapNode($node, $byParent, $user))
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int|string, Collection<int, Module>>  $byParent
     * @return array<string, mixed>|null
     */
    private function mapNode(Module $node, Collection $byParent, User $user): ?array
    {
        if ($node->isLeaf()) {
            if (! $user->hasPermission('read', (int) $node->id)) {
                return null;
            }

            return $this->toArray($node, []);
        }

        $children = $byParent->get($node->id, collect());
        $filtered = $this->filterTree($children, $byParent, $user);

        if ($filtered->isEmpty()) {
            return null;
        }

        return $this->toArray($node, $filtered->all());
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    private function toArray(Module $node, array $children): array
    {
        return [
            'id' => $node->id,
            'name' => $node->name,
            'label' => $node->label,
            'icon' => $node->icon,
            'url' => $node->url,
            'route_name' => $node->route_name,
            'badge_source' => $node->badge_source,
            'external' => (bool) $node->external,
            'children' => $children,
        ];
    }
}
