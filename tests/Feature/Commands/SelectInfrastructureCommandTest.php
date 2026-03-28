<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
});

test('select infrastructure command selects infrastructure successfully', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $organization->id,
        'name' => 'Hetzner Prod',
    ]);

    $infrastructure = Infrastructure::factory()->create([
        'organization_id' => $organization->id,
        'cloud_provider_id' => $provider->id,
        'name' => 'Production',
    ]);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('infrastructure:select')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsQuestion('Select an infrastructure', $infrastructure->id)
        ->expectsOutputToContain('Selected infrastructure [Production]')
        ->assertSuccessful();

    expect($session->getInfrastructure())->not->toBeNull()
        ->and($session->getInfrastructure()?->id)->toBe($infrastructure->id);
});

test('select infrastructure command fails when no providers', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('infrastructure:select')
        ->expectsOutputToContain('No cloud providers configured')
        ->assertFailed();
});

test('select infrastructure command fails when no infrastructures', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $organization->id,
        'name' => 'Hetzner Prod',
    ]);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('infrastructure:select')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsOutputToContain('No infrastructures configured')
        ->assertFailed();
});

test('select infrastructure command fails when not authenticated', function (): void {
    $this->artisan('infrastructure:select')
        ->expectsOutputToContain('You are not logged in')
        ->assertFailed();
});

test('select infrastructure command fails when no organization selected', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);

    $this->artisan('infrastructure:select')
        ->expectsOutputToContain('No organization selected')
        ->assertFailed();
});
