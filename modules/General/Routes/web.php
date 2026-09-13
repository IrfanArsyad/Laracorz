<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\General\App\Http\Controllers\DashboardController;
use Modules\General\App\Http\Controllers\NotificationController;
use Modules\General\App\Http\Controllers\ProfileController;

/*
 * General: halaman personal yang tidak punya entri permission sendiri
 * (Dashboard, Profile, Notification). Fitur ber-permission sudah pindah
 * ke modulnya masing-masing.
 */

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('module.permission:dashboard,read')
        ->name('dashboard');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/sessions', [ProfileController::class, 'logoutOtherSessions'])->name('profile.sessions.logout');
});

Route::middleware(['auth'])
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::patch('/{id}/read', [NotificationController::class, 'markRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    });
