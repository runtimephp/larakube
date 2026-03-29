<?php

declare(strict_types=1);

use App\Data\CreateNetworkData;
use App\Services\MultipassNetworkService;

test('create returns network data with provided values', function (): void {
    $service = new MultipassNetworkService();
    $network = $service->create(new CreateNetworkData(
        name: 'k8s-vpc',
        cidr: '10.0.0.0/16',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));

    expect($network->name)->toBe('k8s-vpc')
        ->and($network->cidr)->toBe('10.0.0.0/16')
        ->and($network->externalId)->toBe('multipass-k8s-vpc');
});

test('list returns empty collection', function (): void {
    $service = new MultipassNetworkService();

    expect($service->list())->toBeEmpty();
});

test('find returns null', function (): void {
    $service = new MultipassNetworkService();

    expect($service->find('any-id'))->toBeNull();
});

test('delete returns true', function (): void {
    $service = new MultipassNetworkService();

    expect($service->delete('any-id'))->toBeTrue();
});
