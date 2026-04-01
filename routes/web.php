<?php

declare(strict_types=1);

use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SwitchOrganizationController;
use App\Http\Middleware\EnsureOrganizationMembership;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::post('organizations/{organization}/switch', [SwitchOrganizationController::class, 'store'])->name('organizations.switch');

    Route::get('dashboard', function () {
        $user = request()->user();
        $organization = $user->currentOrganization ?? $user->organizations()->first();

        if (! $organization) {
            return redirect()->route('organizations.create');
        }

        return redirect("/{$organization->slug}/dashboard");
    })->name('dashboard');
});

Route::middleware(['auth', EnsureOrganizationMembership::class])
    ->prefix('{organization}')
    ->group(function () {
        Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('organizations.dashboard');
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
