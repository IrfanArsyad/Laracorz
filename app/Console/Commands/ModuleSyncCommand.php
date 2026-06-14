<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Services\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleSyncCommand extends Command
{
    protected $signature = 'module:sync';

    protected $description = 'Sinkronisasi modul Nwidart ke tabel module_groups & modules dari Config/menu.php';

    public function handle(ModuleRegistry $registry): int
    {
        $base = base_path('modules');
        if (! File::isDirectory($base)) {
            $this->info('Tidak ada folder modules/. Lewati.');

            return self::SUCCESS;
        }

        $count = 0;
        $modules = collect(File::directories($base));

        DB::transaction(function () use ($modules, &$count): void {
            foreach ($modules as $modulePath) {
                $menuPath = $modulePath.'/Config/menu.php';
                if (! File::exists($menuPath)) {
                    continue;
                }

                $definition = require $menuPath;
                if (! is_array($definition)) {
                    continue;
                }

                $count += $this->syncDefinition($definition);
            }
        });

        $registry->flush();
        $this->info("Selesai. {$count} entri tersinkronisasi.");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function syncDefinition(array $definition): int
    {
        $count = 0;

        foreach ($definition['groups'] ?? [] as $group) {
            ModuleGroup::query()->updateOrCreate(
                ['name' => $group['name']],
                [
                    'label' => $group['label'] ?? Str::headline($group['name']),
                    'icon' => $group['icon'] ?? null,
                    'order' => $group['order'] ?? 0,
                    'active' => $group['active'] ?? true,
                ],
            );
            $count++;
        }

        foreach ($definition['modules'] ?? [] as $entry) {
            $groupName = $entry['group'] ?? null;
            $groupId = $groupName
                ? ModuleGroup::query()->where('name', $groupName)->value('id')
                : null;

            $parentName = $entry['parent'] ?? null;
            $parentId = $parentName
                ? Module::query()->where('name', $parentName)->value('id')
                : null;

            Module::query()->updateOrCreate(
                ['name' => $entry['name']],
                [
                    'parent_id' => $parentId,
                    'module_group_id' => $parentName ? null : $groupId,
                    'label' => $entry['label'] ?? Str::headline($entry['name']),
                    'icon' => $entry['icon'] ?? null,
                    'url' => $entry['url'] ?? null,
                    'route_name' => $entry['route_name'] ?? null,
                    'badge_source' => $entry['badge_source'] ?? null,
                    'extra_actions' => $entry['extra_actions'] ?? null,
                    'external' => $entry['external'] ?? false,
                    'active' => $entry['active'] ?? true,
                    'order' => $entry['order'] ?? 0,
                ],
            );
            $count++;
        }

        return $count;
    }
}
