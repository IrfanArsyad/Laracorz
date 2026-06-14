<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemLog;

class SystemLogService
{
    public function log(
        string $level,
        string $channel,
        string $message,
        array $context = [],
        ?\Throwable $exception = null,
    ): SystemLog {
        return SystemLog::query()->create([
            'level' => $level,
            'channel' => $channel,
            'event' => $context['event'] ?? null,
            'message' => $message,
            'context' => $context ?: null,
            'exception' => $exception
                ? get_class($exception).': '.$exception->getMessage()."\n".$exception->getTraceAsString()
                : null,
            'created_at' => now(),
        ]);
    }
}
