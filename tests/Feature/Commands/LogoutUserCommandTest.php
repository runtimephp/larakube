<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
});

test('logout command clears session', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');

    $session = app(SessionManager::class);
    $session->setUser($userData);

    $this->artisan('user:logout')
        ->expectsOutputToContain('Logged out successfully')
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(0);
});
