<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\App\Http\Controllers\UserController;

Route::middleware(['auth'])
    ->prefix('users')
    ->name('users.')
    ->middleware('module.permission:user-management,read')
    ->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])
            ->middleware('module.permission:user-management,create')
            ->name('store');
        Route::get('/export/csv', [UserController::class, 'exportCsv'])->name('export.csv');
        Route::post('/{user}/restore', [UserController::class, 'restore'])
            ->middleware('module.permission:user-management,update')
            ->name('restore');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('module.permission:user-management,update')
            ->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('module.permission:user-management,delete')
            ->name('destroy');
    });
