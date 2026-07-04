<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes untuk modul General (Dashboard + Profile + Notification + Setting).
 * Auto-prefixed dengan /api oleh bootstrap (lihat routes/api.php).
 */

Route::middleware('auth:sanctum')->prefix('dashboard')->name('api.dashboard.')->group(function (): void {
    //
});

Route::middleware('auth:sanctum')->prefix('profile')->name('api.profile.')->group(function (): void {
    //
});

Route::middleware('auth:sanctum')->prefix('notifications')->name('api.notifications.')->group(function (): void {
    //
});

Route::middleware('auth:sanctum')->prefix('settings')->name('api.settings.')->group(function (): void {
    //
});
