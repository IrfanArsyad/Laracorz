<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('modules')->name('api.modules.')->group(function (): void {
    //
});
