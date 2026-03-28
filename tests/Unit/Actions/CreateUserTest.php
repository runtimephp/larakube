<?php

declare(strict_types=1);

use App\Actions\CreateUser;
use App\Data\CreateUserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->action = app(CreateUser::class);
});

test('creates user',
    /**
     * @throws Throwable
     */
    function (): void {
        $createUserData = new CreateUserData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
        );

        $user = $this->action->handle($createUserData);

        expect($user)
            ->toBeInstanceOf(User::class)
            ->name->toBe('John Doe')
            ->email->toBe('john@example.com')
            ->and($user->id)->toBeString()
            ->and(Hash::check('password123', $user->password))->toBeTrue();
    });

test('persists user to database',
    /**
     * @throws Throwable
     */
    function (): void {
        $createUserData = new CreateUserData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'password123',
        );

        $this->action->handle($createUserData);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    });
