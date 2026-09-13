<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes modul SystemLog. Auto-prefixed dengan /api (lihat routes/api.php).
 * Belum ada endpoint — semua interaksi lewat web (Inertia).
 */

Route::middleware('auth:sanctum')->prefix('system-logs')->name('api.system-logs.')->group(function (): void {
    //
});
