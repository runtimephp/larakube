<?php

declare(strict_types=1);

use App\Data\SessionUserData;

test('constructor sets properties', function (): void {
    $data = new SessionUserData(
        id: 'uuid-123',
        name: 'John Doe',
        email: 'john@example.com',
        token: 'token-abc',
    );

    expect($data)
        ->id->toBe('uuid-123')
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com')
        ->token->toBe('token-abc');
});

test('fromArray and toArray round-trip', function (): void {
    $original = [
        'id' => 'uuid-123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'token' => 'token-abc',
    ];

    $data = SessionUserData::fromArray($original);

    expect($data->toArray())->toBe($original);
});
