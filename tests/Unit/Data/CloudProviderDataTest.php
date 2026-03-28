<?php

declare(strict_types=1);

use App\Data\CloudProviderData;

test('constructor sets properties', function (): void {
    $data = new CloudProviderData(
        id: 'uuid-1',
        name: 'Hetzner Production',
        type: 'hetzner',
        isVerified: true,
    );

    expect($data)
        ->id->toBe('uuid-1')
        ->name->toBe('Hetzner Production')
        ->type->toBe('hetzner')
        ->isVerified->toBeTrue();
});

test('fromArray and toArray round-trip', function (): void {
    $original = [
        'id' => 'uuid-1',
        'name' => 'Hetzner Production',
        'type' => 'hetzner',
        'is_verified' => true,
    ];

    $data = CloudProviderData::fromArray($original);

    expect($data->toArray())->toBe($original);
});
