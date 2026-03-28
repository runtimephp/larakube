<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::prefix('auth')->as('auth.')->group(function (): void {
        Route::post('register', [RegisterController::class, 'store'])->name('register');

        Route::post('token', [AuthTokenController::class, 'store'])->name('token.store');
        Route::delete('token', [AuthTokenController::class, 'destroy'])
            ->middleware('auth:sanctum')
            ->name('token.destroy');
    });
});
