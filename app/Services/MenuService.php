<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;
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

        $roots = Module::query()
            ->active()
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->with(['children' => fn ($q2) => $q2->where('active', true)->orderBy('order')])
                    ->where('active', true)
                    ->orderBy('order');
            }])
            ->orderBy('order')
            ->get()
            ->groupBy('module_group_id');

        $result = [];

        foreach ($groups as $group) {
            $rootList = $roots->get($group->id, collect());
            $filtered = $this->filterTree($rootList, $user);

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
     * @return Collection<int, array<string, mixed>>
     */
    private function filterTree(Collection $nodes, User $user): Collection
    {
        return $nodes
            ->map(fn (Module $node): ?array => $this->mapNode($node, $user))
            ->filter()
            ->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapNode(Module $node, User $user): ?array
    {
        if ($node->isLeaf()) {
            if (! $user->hasPermission('read', (int) $node->id)) {
                return null;
            }

            return $this->toArray($node, []);
        }

        $children = $this->filterTree($node->children, $user);

        if ($children->isEmpty()) {
            return null;
        }

        return $this->toArray($node, $children->all());
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
