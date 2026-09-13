<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes modul ModuleManagement. Auto-prefixed dengan /api (lihat routes/api.php).
 * Belum ada endpoint — semua interaksi lewat web (Inertia).
 */

Route::middleware('auth:sanctum')->prefix('modules')->name('api.modules.')->group(function (): void {
    //
});
