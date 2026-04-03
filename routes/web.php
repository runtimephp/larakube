<?php

declare(strict_types=1);

use App\Data\OrganizationData;
use App\Http\Controllers\OrganizationCloudProvidersController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationGeneralSettingsController;
use App\Http\Controllers\OrganizationLogoController;
use App\Http\Controllers\SwitchOrganizationController;
use App\Http\Controllers\WaitlistController;
use App\Http\Middleware\EnsureOrganizationMembership;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome', [
    'waitlistCount' => DB::table('waitlist_entries')->count(),
]))->name('home');
Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');

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
        Route::prefix('settings')->name('organizations.settings.')->group(function () {
            Route::get('general', [OrganizationGeneralSettingsController::class, 'edit'])->name('general.edit');
            Route::patch('general', [OrganizationGeneralSettingsController::class, 'update'])->name('general.update');
            Route::get('members', fn (Organization $organization) => Inertia::render('organization-settings-placeholder/index', [
                'organization' => OrganizationData::fromModel($organization)->toArray(),
                'section' => [
                    'title' => 'Members',
                    'description' => 'Manage people, roles, and access for this organization.',
                ],
            ]))->name('members');
            Route::get('billing', fn (Organization $organization) => Inertia::render('organization-settings-placeholder/index', [
                'organization' => OrganizationData::fromModel($organization)->toArray(),
                'section' => [
                    'title' => 'Billing',
                    'description' => 'Track plans, invoices, and organization-level billing details.',
                ],
            ]))->name('billing');
            Route::get('cloud-providers', [OrganizationCloudProvidersController::class, 'index'])->name('cloud-providers');
            Route::post('cloud-providers', [OrganizationCloudProvidersController::class, 'store'])->name('cloud-providers.store');
            Route::delete('cloud-providers/{cloudProvider}', [OrganizationCloudProvidersController::class, 'destroy'])->name('cloud-providers.destroy');
            Route::get('danger-zone', fn (Organization $organization) => Inertia::render('organization-settings-placeholder/index', [
                'organization' => OrganizationData::fromModel($organization)->toArray(),
                'section' => [
                    'title' => 'Danger Zone',
                    'description' => 'Review irreversible actions before making organization-wide changes.',
                ],
            ]))->name('danger-zone');
        });
        Route::patch('settings/logo', [OrganizationLogoController::class, 'update'])->name('organizations.logo.update');
    });

if (app()->environment('local')) {
    Route::get('/dev/components', fn () => Inertia::render('dev/components'))->name('dev.components');
}

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
