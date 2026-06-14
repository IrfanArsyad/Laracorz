<?php

declare(strict_types=1);

use Carbon\Carbon;

if (! function_exists('format_date')) {
    function format_date(mixed $value, string $style = 'short'): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        try {
            $date = $value instanceof Carbon ? $value : Carbon::parse($value);
        } catch (\Throwable) {
            return '-';
        }

        $date->locale('id');

        return match ($style) {
            'long' => $date->translatedFormat('d F Y'),
            'datetime' => $date->translatedFormat('d/m/Y H:i'),
            'datetime-long' => $date->translatedFormat('d F Y, H:i'),
            'time' => $date->translatedFormat('H:i'),
            'human' => $date->diffForHumans(),
            default => $date->translatedFormat('d/m/Y'),
        };
    }
}

if (! function_exists('format_currency')) {
    function format_currency(mixed $value, string $currency = 'IDR', int $decimals = 0): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $num = is_numeric($value) ? (float) $value : 0.0;
        $symbol = $currency === 'IDR' ? 'Rp ' : $currency.' ';

        return $symbol.number_format($num, $decimals, ',', '.');
    }
}

if (! function_exists('admin_log')) {
    function admin_log(
        string $action,
        string $description,
        ?\Illuminate\Database\Eloquent\Model $model = null,
        array $old = [],
        array $new = [],
    ): void {
        app(\App\Services\AdminLogService::class)->log(
            action: $action,
            description: $description,
            model: $model,
            old: $old,
            new: $new,
        );
    }
}

if (! function_exists('system_log')) {
    function system_log(
        string $level,
        string $channel,
        string $message,
        array $context = [],
        ?\Throwable $e = null,
    ): void {
        app(\App\Services\SystemLogService::class)->log(
            level: $level,
            channel: $channel,
            message: $message,
            context: $context,
            exception: $e,
        );
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(\App\Services\SettingService::class)->get($key, $default);
    }
}
