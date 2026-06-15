<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('settings')->name('api.settings.')->group(function (): void {
    //
});
