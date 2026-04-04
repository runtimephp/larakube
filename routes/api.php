<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CloudProviderController;
use App\Http\Controllers\Api\V1\InfrastructureController;
use App\Http\Controllers\Api\V1\ManagementClusterController;
use App\Http\Controllers\Api\V1\ManagementClusterKubeconfigController;
use App\Http\Controllers\Api\V1\ManagementClusterReadyController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\RegisterController;
use App\Http\Controllers\Api\V1\ServerController;
use App\Http\Controllers\Api\V1\SyncServerController;
use App\Http\Middleware\ResolveInfrastructure;
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

        Route::apiResource('management-clusters', ManagementClusterController::class)
            ->only(['index', 'store', 'show', 'destroy'])
            ->names('management-clusters');

        Route::patch('management-clusters/{management_cluster}/kubeconfig', [ManagementClusterKubeconfigController::class, 'update'])
            ->name('management-clusters.kubeconfig');

        Route::patch('management-clusters/{management_cluster}/ready', [ManagementClusterReadyController::class, 'update'])
            ->name('management-clusters.ready');

        Route::middleware(ResolveOrganization::class)->group(function (): void {
            Route::apiResource('cloud-providers', CloudProviderController::class)
                ->only(['index', 'store', 'destroy'])
                ->names('cloud-providers');

            Route::apiResource('infrastructures', InfrastructureController::class)
                ->only(['index', 'store'])
                ->names('infrastructures');

            Route::apiResource('servers', ServerController::class)
                ->only(['index', 'show', 'destroy'])
                ->names('servers');

            Route::middleware(ResolveInfrastructure::class)->group(function (): void {
                Route::post('servers', [ServerController::class, 'store'])
                    ->name('servers.store');

                Route::post('servers/sync', [SyncServerController::class, 'store'])
                    ->name('servers.sync');
            });
        });
    });
});
