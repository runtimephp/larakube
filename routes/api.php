<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CloudProviderController;
use App\Http\Controllers\Api\V1\InfrastructureController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\RegisterController;
use App\Http\Middleware\ResolveOrganization;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::prefix('auth')->as('auth.')->group(function (): void {
        Route::post('register', [RegisterController::class, 'store'])->name('register');

        Route::post('token', [AuthTokenController::class, 'store'])->name('token.store');
        Route::delete('token', [AuthTokenController::class, 'destroy'])
            ->middleware('auth:sanctum')
            ->name('token.destroy');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('organizations', OrganizationController::class)
            ->only(['index', 'store'])
            ->names('organizations');

        Route::middleware(ResolveOrganization::class)->group(function (): void {
            Route::apiResource('cloud-providers', CloudProviderController::class)
                ->only(['index', 'store', 'destroy'])
                ->names('cloud-providers');

            Route::apiResource('infrastructures', InfrastructureController::class)
                ->only(['index', 'store'])
                ->names('infrastructures');
        });
    });
});
