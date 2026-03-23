<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/remove-cloud-provider-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('remove cloud provider deletes the selected provider', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $cloudProvider = CloudProvider::factory()->hetzner()->create([
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

    $this->artisan('cloud-provider:remove')
        ->expectsQuestion('Select a cloud provider to remove', $cloudProvider->id)
        ->expectsConfirmation("Are you sure you want to remove [{$cloudProvider->name}]?", 'yes')
        ->expectsOutputToContain('Cloud provider [Hetzner Production] removed')
        ->assertSuccessful();

    $this->assertDatabaseMissing('cloud_providers', ['id' => $cloudProvider->id]);
});

test('remove cloud provider shows message when none exist', function (): void {
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

    $this->artisan('cloud-provider:remove')
        ->expectsOutputToContain('No cloud providers to remove')
        ->assertSuccessful();
});
