<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AccessControl\App\Http\Controllers\ModuleController;
use Modules\AccessControl\App\Http\Controllers\RoleController;
use Modules\AccessControl\App\Http\Controllers\UserController;

/*
 * AccessControl: konsolidasi User + Role + Module.
 * Prefix, name, dan middleware dipertahankan persis seperti modul lama
 * supaya URL/route lama tidak break.
 */

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
