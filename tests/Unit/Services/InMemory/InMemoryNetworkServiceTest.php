<?php

declare(strict_types=1);

use App\Data\CreateNetworkData;
use App\Data\NetworkData;
use App\Services\InMemory\InMemoryNetworkService;

test('delete throws when configured to throw on delete', function (): void {
    $service = new InMemoryNetworkService();
    $service->addNetwork(new NetworkData(externalId: '123', name: 'k8s-vpc', cidr: '10.0.0.0/16'));
    $service->shouldThrowOnDelete();

    $service->delete('123');
})->throws(RuntimeException::class, 'Simulated API failure on delete');

test('create stores and returns network data', function (): void {
    $service = new InMemoryNetworkService();
    $network = $service->create(new CreateNetworkData(
        name: 'k8s-vpc',
        cidr: '10.0.0.0/16',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));

    expect($network->name)->toBe('k8s-vpc')
        ->and($network->cidr)->toBe('10.0.0.0/16')
        ->and($service->list())->toHaveCount(1);
});

test('create throws when configured to fail', function (): void {
    $service = new InMemoryNetworkService();
    $service->shouldFailCreate();

    $service->create(new CreateNetworkData(
        name: 'k8s-vpc',
        cidr: '10.0.0.0/16',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));
})->throws(RuntimeException::class);

test('list returns all networks', function (): void {
    $service = new InMemoryNetworkService();
    $service->addNetwork(new NetworkData(externalId: '1', name: 'vpc-1', cidr: '10.0.0.0/16'));
    $service->addNetwork(new NetworkData(externalId: '2', name: 'vpc-2', cidr: '10.1.0.0/16'));

    expect($service->list())->toHaveCount(2);
});

test('find returns network by id', function (): void {
    $service = new InMemoryNetworkService();
    $service->addNetwork(new NetworkData(externalId: '123', name: 'k8s-vpc', cidr: '10.0.0.0/16'));

    $network = $service->find('123');

    expect($network)->not->toBeNull()
        ->and($network->name)->toBe('k8s-vpc');
});

test('find returns null when not found', function (): void {
    $service = new InMemoryNetworkService();

    expect($service->find('nonexistent'))->toBeNull();
});

test('delete removes network and returns true', function (): void {
    $service = new InMemoryNetworkService();
    $service->addNetwork(new NetworkData(externalId: '123', name: 'k8s-vpc', cidr: '10.0.0.0/16'));

    expect($service->delete('123'))->toBeTrue()
        ->and($service->list())->toBeEmpty();
});

test('delete returns false when not found', function (): void {
    $service = new InMemoryNetworkService();

    expect($service->delete('nonexistent'))->toBeFalse();
});

test('delete returns false when configured to fail', function (): void {
    $service = new InMemoryNetworkService();
    $service->addNetwork(new NetworkData(externalId: '123', name: 'k8s-vpc', cidr: '10.0.0.0/16'));
    $service->shouldFailDelete();

    expect($service->delete('123'))->toBeFalse();
});
