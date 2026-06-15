<?php

declare(strict_types=1);

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

/*
 * Auto-load route file dari setiap module: modules/{Module}/Routes/web.php
 * Jadi routes core tetap kecil, route per fitur tinggal di-add via modul.
 */
foreach (glob(base_path('modules/*/Routes/web.php')) ?: [] as $routeFile) {
    require $routeFile;
}
