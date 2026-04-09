<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ProviderOverviewController;
use App\Http\Controllers\Admin\ProviderRegionsController;
use App\Http\Controllers\Admin\ProviderRegionSyncController;
use App\Http\Controllers\Admin\ProviderSettingsController;
use App\Http\Controllers\AdminManagementClusterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::prefix('management-clusters')->as('management-clusters.')->group(function () {
            Route::get('/', [AdminManagementClusterController::class, 'index'])
                ->name('index');
            Route::get('/create', [AdminManagementClusterController::class, 'create'])
                ->name('create');
            Route::get('/{management_cluster}', [AdminManagementClusterController::class, 'show'])
                ->name('show');
            Route::post('/', [AdminManagementClusterController::class, 'store'])
                ->name('store');
        });

        Route::prefix('settings')->as('settings.')->group(function () {
            Route::prefix('providers')->as('providers.')->group(function () {
                Route::get('/', [ProviderController::class, 'index'])->name('index');
                Route::post('/', [ProviderController::class, 'store'])->name('store');
                Route::get('/{provider}', [ProviderController::class, 'show'])->name('show');
                Route::patch('/{provider}', [ProviderController::class, 'update'])->name('update');
                Route::get('/{provider}/overview', [ProviderOverviewController::class, 'show'])->name('overview');
                Route::get('/{provider}/regions', [ProviderRegionsController::class, 'show'])->name('regions');
                Route::get('/{provider}/settings', [ProviderSettingsController::class, 'show'])->name('settings');
                Route::post('/{provider}/sync-regions', [ProviderRegionSyncController::class, 'store'])->name('sync-regions');
            });
        });
    });
