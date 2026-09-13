<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\RoleManagement\App\Http\Controllers\RoleController;

Route::middleware(['auth'])
    ->prefix('roles')
    ->name('roles.')
    ->middleware('module.permission:role-management,read')
    ->group(function (): void {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])
            ->middleware('module.permission:role-management,create')
            ->name('store');
        Route::put('/{role}', [RoleController::class, 'update'])
            ->middleware('module.permission:role-management,update')
            ->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->middleware('module.permission:role-management,delete')
            ->name('destroy');
    });
