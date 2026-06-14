<?php

declare(strict_types=1);

use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('module.permission:dashboard,read')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/sessions', [ProfileController::class, 'logoutOtherSessions'])->name('profile.sessions.logout');

    // Role Management
    Route::prefix('roles')->name('roles.')
        ->middleware('module.permission:role-management,read')
        ->group(function (): void {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->middleware('module.permission:role-management,create')->name('create');
            Route::post('/', [RoleController::class, 'store'])->middleware('module.permission:role-management,create')->name('store');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->middleware('module.permission:role-management,update')->name('edit');
            Route::put('/{role}', [RoleController::class, 'update'])->middleware('module.permission:role-management,update')->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('module.permission:role-management,delete')->name('destroy');
        });

    // Module Management
    Route::prefix('modules')->name('modules.')
        ->middleware('module.permission:module-management,read')
        ->group(function (): void {
            Route::get('/', [ModuleController::class, 'index'])->name('index');
            Route::get('/create', [ModuleController::class, 'create'])->middleware('module.permission:module-management,create')->name('create');
            Route::post('/', [ModuleController::class, 'store'])->middleware('module.permission:module-management,create')->name('store');
            Route::get('/{module}/edit', [ModuleController::class, 'edit'])->middleware('module.permission:module-management,update')->name('edit');
            Route::put('/{module}', [ModuleController::class, 'update'])->middleware('module.permission:module-management,update')->name('update');
            Route::delete('/{module}', [ModuleController::class, 'destroy'])->middleware('module.permission:module-management,delete')->name('destroy');
            Route::post('/groups', [ModuleController::class, 'storeGroup'])->middleware('module.permission:module-management,create')->name('groups.store');
            Route::put('/groups/{group}', [ModuleController::class, 'updateGroup'])->middleware('module.permission:module-management,update')->name('groups.update');
            Route::delete('/groups/{group}', [ModuleController::class, 'destroyGroup'])->middleware('module.permission:module-management,delete')->name('groups.destroy');
        });

    // User Management
    Route::prefix('users')->name('users.')
        ->middleware('module.permission:user-management,read')
        ->group(function (): void {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->middleware('module.permission:user-management,create')->name('create');
            Route::post('/', [UserController::class, 'store'])->middleware('module.permission:user-management,create')->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->middleware('module.permission:user-management,update')->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->middleware('module.permission:user-management,update')->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('module.permission:user-management,delete')->name('destroy');
            Route::post('/{user}/restore', [UserController::class, 'restore'])->middleware('module.permission:user-management,update')->name('restore');
            Route::get('/export/csv', [UserController::class, 'exportCsv'])->name('export.csv');
        });

    // Admin Log
    Route::prefix('admin-logs')->name('admin-logs.')
        ->middleware('module.permission:admin-log,read')
        ->group(function (): void {
            Route::get('/', [AdminLogController::class, 'index'])->name('index');
        });

    // System Log
    Route::prefix('system-logs')->name('system-logs.')
        ->middleware('module.permission:system-log,read')
        ->group(function (): void {
            Route::get('/', [SystemLogController::class, 'index'])->name('index');
        });

    // Settings
    Route::prefix('settings')->name('settings.')
        ->middleware('module.permission:settings,read')
        ->group(function (): void {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/', [SettingController::class, 'update'])->middleware('module.permission:settings,update')->name('update');
        });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::patch('/{id}/read', [NotificationController::class, 'markRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    });
});

require __DIR__.'/auth.php';
