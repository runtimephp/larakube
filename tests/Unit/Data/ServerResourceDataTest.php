<?php

declare(strict_types=1);

use App\Data\ServerResourceData;

test('constructor sets properties', function (): void {
    $data = new ServerResourceData(
        id: 'srv-1',
        name: 'web-1',
        status: 'running',
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
        ipv6: null,
        externalId: '123',
        cloudProviderId: 'cp-1',
        infrastructureId: 'infra-1',
    );

    expect($data)
        ->id->toBe('srv-1')
        ->name->toBe('web-1')
        ->status->toBe('running')
        ->externalId->toBe('123');
});

test('fromArray and toArray round-trip', function (): void {
    $original = [
        'id' => 'srv-1',
        'name' => 'web-1',
        'status' => 'running',
        'type' => 'cx11',
        'region' => 'fsn1',
        'ipv4' => '1.2.3.4',
        'ipv6' => null,
        'external_id' => '123',
        'cloud_provider_id' => 'cp-1',
        'infrastructure_id' => 'infra-1',
    ];

    $data = ServerResourceData::fromArray($original);

    expect($data->toArray())->toBe($original);
});
