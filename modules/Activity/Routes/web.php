<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Activity\App\Http\Controllers\AdminLogController;
use Modules\Activity\App\Http\Controllers\SystemLogController;

/*
 * Activity: konsolidasi AdminLog + SystemLog.
 * Prefix, name, dan middleware dipertahankan persis seperti modul lama.
 */

Route::middleware(['auth'])
    ->prefix('admin-logs')
    ->name('admin-logs.')
    ->middleware('module.permission:admin-log,read')
    ->group(function (): void {
        Route::get('/', [AdminLogController::class, 'index'])->name('index');
    });

Route::middleware(['auth'])
    ->prefix('system-logs')
    ->name('system-logs.')
    ->middleware('module.permission:system-log,read')
    ->group(function (): void {
        Route::get('/', [SystemLogController::class, 'index'])->name('index');
    });
