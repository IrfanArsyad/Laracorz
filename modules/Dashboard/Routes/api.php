<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('dashboard')->name('api.dashboard.')->group(function (): void {
    //
});
