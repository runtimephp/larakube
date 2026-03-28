<?php

declare(strict_types=1);

use App\Data\UserData;

test('constructor sets properties', function (): void {
    $data = new UserData(
        id: 'uuid-123',
        name: 'John Doe',
        email: 'john@example.com',
    );

    expect($data)
        ->id->toBe('uuid-123')
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com');
});

test('fromArray and toArray round-trip', function (): void {
    $original = [
        'id' => 'uuid-123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    $data = UserData::fromArray($original);

    expect($data->toArray())->toBe($original);
});
