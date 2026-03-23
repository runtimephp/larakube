<?php

declare(strict_types=1);

use App\Data\SessionOrganizationData;

test('constructor sets properties', function (): void {
    $data = new SessionOrganizationData(
        id: 'uuid-456',
        name: 'Acme Corp',
        slug: 'acme-corp',
    );

    expect($data)
        ->id->toBe('uuid-456')
        ->name->toBe('Acme Corp')
        ->slug->toBe('acme-corp');
});

test('fromArray and toArray round-trip', function (): void {
    $original = [
        'id' => 'uuid-456',
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
    ];

    $data = SessionOrganizationData::fromArray($original);

    expect($data->toArray())->toBe($original);
});
