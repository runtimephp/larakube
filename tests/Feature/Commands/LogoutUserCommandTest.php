<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Models\User;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/logout-cmd-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('logout command clears session', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $userData = new LoginUser()->handle('john@example.com', 'password123');

    $session = app(SessionManager::class);
    $session->setUser($userData);

    $this->artisan('user:logout')
        ->expectsOutputToContain('Logged out successfully')
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(0);
});
