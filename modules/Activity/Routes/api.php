<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes untuk modul Activity (AdminLog + SystemLog).
 * Auto-prefixed dengan /api oleh bootstrap (lihat routes/api.php).
 */

Route::middleware('auth:sanctum')->prefix('admin-logs')->name('api.admin-logs.')->group(function (): void {
    //
});

Route::middleware('auth:sanctum')->prefix('system-logs')->name('api.system-logs.')->group(function (): void {
    //
});
