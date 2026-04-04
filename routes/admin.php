<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ManagementClusterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::prefix('management-clusters')->as('management-clusters.')->group(function () {
            Route::get('/', [ManagementClusterController::class, 'index'])->name('index');
        });
    });
