<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Actions\LogoutUser;
use App\Console\Services\SessionManager;
use App\Models\User;

beforeEach(function (): void {
    $this->sessionsPath = sys_get_temp_dir().'/logout-test-'.uniqid();
    $this->apiUrl = 'http://localhost:8000';
});

afterEach(function (): void {
    $files = glob($this->sessionsPath.'/*.json');
    if (is_array($files)) {
        array_map(unlink(...), $files);
    }

    if (is_dir($this->sessionsPath)) {
        rmdir($this->sessionsPath);
    }
});

test('logout clears session and revokes tokens',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');

        $session = new SessionManager($this->sessionsPath, $this->apiUrl);
        $session->setUser($userData);

        expect($session->isAuthenticated())->toBeTrue();

        app(LogoutUser::class)->handle($session);

        expect($session->isAuthenticated())->toBeFalse()
            ->and($session->getUser())->toBeNull()
            ->and($user->tokens()->count())->toBe(0);
    });

test('logout handles missing user gracefully',
    /**
     * @throws Throwable
     */
    function (): void {
        $session = new SessionManager($this->sessionsPath, $this->apiUrl);

        app(LogoutUser::class)->handle($session);

        expect($session->isAuthenticated())->toBeFalse();
    });
