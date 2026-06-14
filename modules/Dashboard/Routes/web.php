<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('module.permission:dashboard,read')
        ->name('dashboard');
});
