<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/list-cloud-provider-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('list cloud providers shows table of providers', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    CloudProvider::factory()->hetzner()->create([
        'organization_id' => $organization->id,
        'name' => 'Hetzner Production',
    ]);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('cloud-provider:list')
        ->expectsOutputToContain('Hetzner Production')
        ->assertSuccessful();
});

test('list cloud providers shows message when none exist', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('cloud-provider:list')
        ->expectsOutputToContain('No cloud providers configured')
        ->assertSuccessful();
});
