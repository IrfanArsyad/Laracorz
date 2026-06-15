<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('profile')->name('api.profile.')->group(function (): void {
    //
});
