<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AdminLog\App\Http\Controllers\AdminLogController;

Route::middleware(['auth'])
    ->prefix('admin-logs')
    ->name('admin-logs.')
    ->middleware('module.permission:admin-log,read')
    ->group(function (): void {
        Route::get('/', [AdminLogController::class, 'index'])->name('index');
    });
