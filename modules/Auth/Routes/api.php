<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('api.auth.')->group(function (): void {
    // Route::post('/login', [...]);
    // Route::post('/logout', [...])->middleware('auth:sanctum');
});
