<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('roles')->name('api.roles.')->group(function (): void {
    //
});
