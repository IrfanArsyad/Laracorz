<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes modul UserManagement. Auto-prefixed dengan /api (lihat routes/api.php).
 * Belum ada endpoint — semua interaksi lewat web (Inertia).
 */

Route::middleware('auth:sanctum')->prefix('users')->name('api.users.')->group(function (): void {
    //
});
