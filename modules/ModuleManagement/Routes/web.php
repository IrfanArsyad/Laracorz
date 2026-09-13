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
        Route::post('/', [ModuleController::class, 'store'])
            ->middleware('module.permission:module-management,create')
            ->name('store');
        Route::put('/{module}', [ModuleController::class, 'update'])
            ->middleware('module.permission:module-management,update')
            ->name('update');
        Route::delete('/{module}', [ModuleController::class, 'destroy'])
            ->middleware('module.permission:module-management,delete')
            ->name('destroy');
        Route::patch('/{module}/toggle', [ModuleController::class, 'toggleActive'])
            ->middleware('module.permission:module-management,update')
            ->name('toggle');
        Route::post('/{module}/move/{direction}', [ModuleController::class, 'move'])
            ->where('direction', 'up|down')
            ->middleware('module.permission:module-management,update')
            ->name('move');
        Route::post('/reorder', [ModuleController::class, 'reorder'])
            ->middleware('module.permission:module-management,update')
            ->name('reorder');
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
