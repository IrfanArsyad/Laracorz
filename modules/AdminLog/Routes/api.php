<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('admin-logs')->name('api.admin-logs.')->group(function (): void {
    //
});
