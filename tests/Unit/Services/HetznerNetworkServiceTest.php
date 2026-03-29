<?php

declare(strict_types=1);

use App\Data\CreateNetworkData;
use App\Services\HetznerNetworkService;
use Illuminate\Support\Facades\Http;

test('create throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks' => Http::response([
            'error' => ['message' => 'ip_range is invalid', 'code' => 'invalid_input'],
        ], 422),
    ]);

    $service = new HetznerNetworkService('token');

    $service->create(new CreateNetworkData(
        name: 'k8s-vpc',
        cidr: 'invalid',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));
})->throws(RuntimeException::class, 'ip_range is invalid');

test('list returns collection of network data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks' => Http::response([
            'networks' => [
                [
                    'id' => 123,
                    'name' => 'k8s-vpc',
                    'ip_range' => '10.0.0.0/16',
                ],
                [
                    'id' => 456,
                    'name' => 'db-vpc',
                    'ip_range' => '10.1.0.0/16',
                ],
            ],
        ]),
    ]);

    $service = new HetznerNetworkService('token');
    $networks = $service->list();

    expect($networks)->toHaveCount(2)
        ->and($networks[0]->externalId)->toBe(123)
        ->and($networks[0]->name)->toBe('k8s-vpc')
        ->and($networks[1]->externalId)->toBe(456);
});

test('list throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks' => Http::response([
            'error' => ['message' => 'unauthorized', 'code' => 'unauthorized'],
        ], 401),
    ]);

    $service = new HetznerNetworkService('token');

    $service->list();
})->throws(RuntimeException::class, 'unauthorized');

test('find returns network data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks/123' => Http::response([
            'network' => [
                'id' => 123,
                'name' => 'k8s-vpc',
                'ip_range' => '10.0.0.0/16',
            ],
        ]),
    ]);

    $service = new HetznerNetworkService('token');
    $network = $service->find('123');

    expect($network)->not->toBeNull()
        ->and($network->externalId)->toBe(123)
        ->and($network->name)->toBe('k8s-vpc');
});

test('find returns null when not found', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks/999' => Http::response([
            'error' => ['message' => 'not found', 'code' => 'not_found'],
        ], 404),
    ]);

    $service = new HetznerNetworkService('token');

    expect($service->find('999'))->toBeNull();
});

test('delete returns true on success', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks/123' => Http::response([], 200),
    ]);

    $service = new HetznerNetworkService('token');

    expect($service->delete('123'))->toBeTrue();
});

test('delete returns false on failure', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks/999' => Http::response([], 404),
    ]);

    $service = new HetznerNetworkService('token');

    expect($service->delete('999'))->toBeFalse();
});

test('create returns network data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/networks' => Http::response([
            'network' => [
                'id' => 123,
                'name' => 'k8s-vpc',
                'ip_range' => '10.0.0.0/16',
            ],
        ]),
    ]);

    $service = new HetznerNetworkService('token');
    $network = $service->create(new CreateNetworkData(
        name: 'k8s-vpc',
        cidr: '10.0.0.0/16',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));

    expect($network->externalId)->toBe(123)
        ->and($network->name)->toBe('k8s-vpc')
        ->and($network->cidr)->toBe('10.0.0.0/16');
});
