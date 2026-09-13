<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Settings\App\Http\Controllers\SettingController;

Route::middleware(['auth'])
    ->prefix('settings')
    ->name('settings.')
    ->middleware('module.permission:settings,read')
    ->group(function (): void {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'update'])
            ->middleware('module.permission:settings,update')
            ->name('update');
    });
