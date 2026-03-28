<?php

declare(strict_types=1);

use App\Data\InfrastructureData;

test('constructor sets properties', function (): void {
    $data = new InfrastructureData(
        id: 'uuid-1',
        name: 'Production',
        description: 'Prod infra',
        status: 'healthy',
        cloudProviderId: 'cp-1',
    );

    expect($data)
        ->id->toBe('uuid-1')
        ->name->toBe('Production')
        ->description->toBe('Prod infra')
        ->status->toBe('healthy')
        ->cloudProviderId->toBe('cp-1');
});

test('fromArray and toArray round-trip', function (): void {
    $original = [
        'id' => 'uuid-1',
        'name' => 'Production',
        'description' => 'Prod infra',
        'status' => 'healthy',
        'cloud_provider_id' => 'cp-1',
    ];

    $data = InfrastructureData::fromArray($original);

    expect($data->toArray())->toBe($original);
});
