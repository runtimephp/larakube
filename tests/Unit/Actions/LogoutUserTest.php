<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Actions\LogoutUser;
use App\Console\Services\SessionManager;
use App\Models\User;

beforeEach(function (): void {
    $this->tempDir = sys_get_temp_dir().'/logout-test-'.uniqid();
    mkdir($this->tempDir, 0700, true);
    $this->tempPath = $this->tempDir.'/session.json';
});

afterEach(function (): void {
    if (file_exists($this->tempPath)) {
        unlink($this->tempPath);
    }

    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

test('logout clears session and revokes tokens',
    /**
     * @throws Throwable
     */
    function (): void {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');

        $session = new SessionManager($this->tempPath);
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
        $session = new SessionManager($this->tempPath);

        app(LogoutUser::class)->handle($session);

        expect($session->isAuthenticated())->toBeFalse();
    });
