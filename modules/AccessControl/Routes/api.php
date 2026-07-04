<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes untuk modul AccessControl (User + Role + Module).
 * Auto-prefixed dengan /api oleh bootstrap (lihat routes/api.php).
 */

Route::middleware('auth:sanctum')->prefix('users')->name('api.users.')->group(function (): void {
    //
});

Route::middleware('auth:sanctum')->prefix('roles')->name('api.roles.')->group(function (): void {
    //
});

Route::middleware('auth:sanctum')->prefix('modules')->name('api.modules.')->group(function (): void {
    //
});
