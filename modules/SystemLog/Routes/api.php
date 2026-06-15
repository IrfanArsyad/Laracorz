<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('system-logs')->name('api.system-logs.')->group(function (): void {
    //
});
