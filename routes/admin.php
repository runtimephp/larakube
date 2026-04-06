<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ManagementClusterController;
use App\Http\Controllers\Admin\ProviderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::prefix('management-clusters')->as('management-clusters.')->group(function () {
            Route::get('/', [ManagementClusterController::class, 'index'])->name('index');
            Route::get('/{management_cluster}', [ManagementClusterController::class, 'show'])->name('show');
        });

        Route::prefix('settings')->as('settings.')->group(function () {
            Route::prefix('providers')->as('providers.')->group(function () {
                Route::get('/', [ProviderController::class, 'index'])->name('index');
                Route::get('/{provider}', [ProviderController::class, 'show'])->name('show');
                Route::patch('/{provider}', [ProviderController::class, 'update'])->name('update');
            });
        });
    });
