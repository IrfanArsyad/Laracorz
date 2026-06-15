<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes untuk modul UserManagement.
 * Auto-prefixed dengan /api oleh bootstrap (lihat routes/api.php).
 *
 * Contoh:
 *   Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'apiIndex']);
 */
Route::middleware('auth:sanctum')->prefix('users')->name('api.users.')->group(function (): void {
    // Route::get('/', [UserController::class, 'apiIndex'])->name('index');
});
