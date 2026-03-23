<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/auth-cmd-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('authenticated command blocks unauthenticated users', function (): void {
    $this->artisan('organization:select')
        ->expectsOutputToContain('You are not logged in')
        ->assertFailed();
});

test('authenticated command allows authenticated users', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization, ['role' => 'owner']);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);

    $this->artisan('organization:select')
        ->expectsQuestion('Select an organization', $organization->id)
        ->assertSuccessful();
});

test('authenticated command detects expired token', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);

    // Revoke all tokens to simulate expiry
    $user->tokens()->delete();

    $this->artisan('organization:select')
        ->expectsOutputToContain('Your session has expired')
        ->assertFailed();
});
