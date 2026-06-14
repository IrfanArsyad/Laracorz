<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\ModuleManagement\App\Http\Controllers\ModuleController;

Route::middleware(['auth'])
    ->prefix('modules')
    ->name('modules.')
    ->middleware('module.permission:module-management,read')
    ->group(function (): void {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/create', [ModuleController::class, 'create'])
            ->middleware('module.permission:module-management,create')
            ->name('create');
        Route::post('/', [ModuleController::class, 'store'])
            ->middleware('module.permission:module-management,create')
            ->name('store');
        Route::get('/{module}/edit', [ModuleController::class, 'edit'])
            ->middleware('module.permission:module-management,update')
            ->name('edit');
        Route::put('/{module}', [ModuleController::class, 'update'])
            ->middleware('module.permission:module-management,update')
            ->name('update');
        Route::delete('/{module}', [ModuleController::class, 'destroy'])
            ->middleware('module.permission:module-management,delete')
            ->name('destroy');
        Route::post('/groups', [ModuleController::class, 'storeGroup'])
            ->middleware('module.permission:module-management,create')
            ->name('groups.store');
        Route::put('/groups/{group}', [ModuleController::class, 'updateGroup'])
            ->middleware('module.permission:module-management,update')
            ->name('groups.update');
        Route::delete('/groups/{group}', [ModuleController::class, 'destroyGroup'])
            ->middleware('module.permission:module-management,delete')
            ->name('groups.destroy');
    });
