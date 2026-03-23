<?php

declare(strict_types=1);

use App\Actions\CreateUser;
use App\Data\CreateUserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('create user', function (): void {
    $createUserData = new CreateUserData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
    );

    $user = new CreateUser()->handle($createUserData);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com')
        ->and($user->id)->toBeString()
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

test('create user persists to database', function (): void {
    $createUserData = new CreateUserData(
        name: 'Jane Doe',
        email: 'jane@example.com',
        password: 'password123',
    );

    new CreateUser()->handle($createUserData);

    $this->assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});
