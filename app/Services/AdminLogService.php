<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdminLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminLogService
{
    public function log(
        string $action,
        string $description,
        ?Model $model = null,
        array $old = [],
        array $new = [],
        ?string $module = null,
    ): ?AdminLog {
        $user = Auth::user();
        $request = Request::instance();

        return AdminLog::query()->create([
            'user_id' => $user?->getKey(),
            'user_name' => $user?->name,
            'module' => $module ?? ($model ? $this->guessModule($model) : null),
            'action' => $action,
            'description' => $description,
            'loggable_id' => $model?->getKey(),
            'loggable_type' => $model ? $model::class : null,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'url' => substr((string) $request->fullUrl(), 0, 255),
            'method' => $request->method(),
            'created_at' => now(),
        ]);
    }

    private function guessModule(Model $model): string
    {
        return strtolower(class_basename($model));
    }
}
