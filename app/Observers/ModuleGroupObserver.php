<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ModuleGroup;
use App\Services\ModuleRegistry;
use Illuminate\Support\Facades\Cache;

class ModuleGroupObserver
{
    public function __construct(private readonly ModuleRegistry $registry) {}

    public function saved(ModuleGroup $group): void
    {
        $this->flush();
    }

    public function deleted(ModuleGroup $group): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        $this->registry->flush();
        Cache::flush();
    }
}
