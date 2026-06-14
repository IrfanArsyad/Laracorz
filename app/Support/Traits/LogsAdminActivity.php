<?php

declare(strict_types=1);

namespace App\Support\Traits;

use App\Models\AdminLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsAdminActivity
{
    protected static function bootLogsAdminActivity(): void
    {
        static::created(fn (Model $m) => static::writeAdminLog($m, 'created'));
        static::updated(fn (Model $m) => static::writeAdminLog($m, 'updated'));
        static::deleted(fn (Model $m) => static::writeAdminLog($m, 'deleted'));
        if (method_exists(static::class, 'restored')) {
            static::restored(fn (Model $m) => static::writeAdminLog($m, 'restored'));
        }
    }

    protected static function writeAdminLog(Model $model, string $action): void
    {
        $exclude = ['password', 'remember_token', 'updated_at', 'created_at'];
        if (property_exists($model, 'logExcept')) {
            /** @var array<int, string> $logExcept */
            $logExcept = $model->logExcept;
            $exclude = array_merge($exclude, $logExcept);
        }

        $original = $model->getOriginal();
        $changes = $model->getChanges();

        $old = collect($original)->only(array_keys($changes))->except($exclude)->all();
        $new = collect($changes)->except($exclude)->all();

        $user = Auth::user();
        $request = Request::instance();

        AdminLog::query()->create([
            'user_id' => $user?->getKey(),
            'user_name' => $user?->name,
            'module' => strtolower(class_basename($model)),
            'action' => $action,
            'description' => $action.' '.class_basename($model).' '.($model->getKey() ?? ''),
            'loggable_id' => $model->getKey(),
            'loggable_type' => $model::class,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'url' => substr((string) $request->fullUrl(), 0, 255),
            'method' => $request->method(),
            'created_at' => now(),
        ]);
    }
}
