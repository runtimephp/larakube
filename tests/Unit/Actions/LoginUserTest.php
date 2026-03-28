<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Data\SessionUserData;
use App\Models\User;

beforeEach(function (): void {
    $this->loginUser = app(LoginUser::class);
});

test('successful login returns session user data with token',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $result = $this->loginUser->handle('john@example.com', 'password123');

        expect($result)
            ->toBeInstanceOf(SessionUserData::class)
            ->id->toBe($user->id)
            ->name->toBe($user->name)
            ->email->toBe('john@example.com')
            ->token->toBeString()
            ->token->not->toBeEmpty();
    });

test('wrong password returns null',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $result = $this->loginUser->handle('john@example.com', 'wrong-password');

        expect($result)->toBeNull();
    });

test('non-existent email returns null',
    function (): void {
        $result = $this->loginUser->handle('nobody@example.com', 'password123');

        expect($result)->toBeNull();
    });

test('login creates a sanctum token in the database',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->loginUser->handle('john@example.com', 'password123');

        expect($user->tokens()->count())->toBe(1);
    });
