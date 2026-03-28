<?php

declare(strict_types=1);

use App\Data\OrganizationData;

test('constructor sets properties', function (): void {
    $data = new OrganizationData(
        id: 'uuid-123',
        name: 'Acme Corp',
        slug: 'acme-corp',
        description: 'A great company',
    );

    expect($data)
        ->id->toBe('uuid-123')
        ->name->toBe('Acme Corp')
        ->slug->toBe('acme-corp')
        ->description->toBe('A great company');
});

test('fromArray and toArray round-trip', function (): void {
    $original = [
        'id' => 'uuid-123',
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
        'description' => 'A great company',
    ];

    $data = OrganizationData::fromArray($original);

    expect($data->toArray())->toBe($original);
});

test('description defaults to null', function (): void {
    $data = OrganizationData::fromArray([
        'id' => 'uuid-123',
        'name' => 'Acme',
        'slug' => 'acme',
    ]);

    expect($data->description)->toBeNull();
});
