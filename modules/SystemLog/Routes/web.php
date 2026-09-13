<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\SystemLog\App\Http\Controllers\SystemLogController;

Route::middleware(['auth'])
    ->prefix('system-logs')
    ->name('system-logs.')
    ->middleware('module.permission:system-log,read')
    ->group(function (): void {
        Route::get('/', [SystemLogController::class, 'index'])->name('index');
    });
