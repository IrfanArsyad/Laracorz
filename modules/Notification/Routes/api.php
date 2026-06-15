<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('notifications')->name('api.notifications.')->group(function (): void {
    //
});
