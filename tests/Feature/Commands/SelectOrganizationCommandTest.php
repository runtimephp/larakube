<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
});

test('select organization command lists and persists selection', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create(['name' => 'Acme Corp']);
    $user->organizations()->attach($organization, ['role' => 'member']);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);

    $this->artisan('organization:select')
        ->expectsQuestion('Select an organization', $organization->id)
        ->expectsOutputToContain('Selected organization [Acme Corp]')
        ->assertSuccessful();

    expect($session->hasOrganization())->toBeTrue()
        ->and($session->getOrganization()->id)->toBe($organization->id);
});

test('select organization command fails when user has no orgs', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);

    $this->artisan('organization:select')
        ->expectsOutputToContain('You do not belong to any organizations')
        ->assertFailed();
});

test('select organization command fails when not authenticated', function (): void {
    $this->artisan('organization:select')
        ->expectsOutputToContain('You are not logged in')
        ->assertFailed();
});
