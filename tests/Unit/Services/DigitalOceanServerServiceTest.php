<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Enums\ServerStatus;
use App\Services\DigitalOceanServerService;
use Illuminate\Support\Facades\Http;

test('get all returns collection of server data', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response([
            'droplets' => [
                [
                    'id' => 100,
                    'name' => 'web-1',
                    'status' => 'active',
                    'size' => ['slug' => 's-1vcpu-1gb'],
                    'region' => ['slug' => 'nyc1'],
                    'networks' => [
                        'v4' => [
                            ['ip_address' => '10.0.0.1', 'type' => 'private'],
                            ['ip_address' => '1.2.3.4', 'type' => 'public'],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $service = new DigitalOceanServerService('token');
    $servers = $service->getAll();

    expect($servers)->toHaveCount(1)
        ->and($servers[0]->externalId)->toBe(100)
        ->and($servers[0]->name)->toBe('web-1')
        ->and($servers[0]->status)->toBe(ServerStatus::Running)
        ->and($servers[0]->type)->toBe('s-1vcpu-1gb')
        ->and($servers[0]->region)->toBe('nyc1')
        ->and($servers[0]->ipv4)->toBe('1.2.3.4');
});

test('create returns server data', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response([
            'droplet' => [
                'id' => 200,
                'name' => 'web-2',
                'status' => 'new',
                'size' => ['slug' => 's-2vcpu-2gb'],
                'region' => ['slug' => 'sfo1'],
                'networks' => ['v4' => []],
            ],
        ]),
    ]);

    $service = new DigitalOceanServerService('token');
    $server = $service->create(new CreateServerData(
        name: 'web-2',
        type: 's-2vcpu-2gb',
        image: 'ubuntu-22.04',
        region: 'sfo1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));

    expect($server->externalId)->toBe(200)
        ->and($server->name)->toBe('web-2')
        ->and($server->status)->toBe(ServerStatus::Starting)
        ->and($server->ipv4)->toBeNull();
});

test('create throws on api error', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response([
            'id' => 'bad_request',
            'message' => 'Name is required',
        ], 422),
    ]);

    $service = new DigitalOceanServerService('token');

    $service->create(new CreateServerData(
        name: '',
        type: 's-1vcpu-1gb',
        image: 'ubuntu-22.04',
        region: 'nyc1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));
})->throws(RuntimeException::class, 'Name is required');

test('get all throws on api error', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets' => Http::response([
            'id' => 'unauthorized',
            'message' => 'Unable to authenticate you',
        ], 401),
    ]);

    $service = new DigitalOceanServerService('token');

    $service->getAll();
})->throws(RuntimeException::class, 'Unable to authenticate you');

test('find returns server data', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets*' => Http::response([
            'droplets' => [
                [
                    'id' => 300,
                    'name' => 'web-1',
                    'status' => 'active',
                    'size' => ['slug' => 's-1vcpu-1gb'],
                    'region' => ['slug' => 'nyc1'],
                    'networks' => ['v4' => [['ip_address' => '1.2.3.4', 'type' => 'public']]],
                ],
            ],
        ]),
    ]);

    $service = new DigitalOceanServerService('token');
    $server = $service->find('web-1');

    expect($server)->not->toBeNull()
        ->and($server->name)->toBe('web-1');
});

test('find throws on api error', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets*' => Http::response([
            'id' => 'forbidden',
            'message' => 'You do not have access for the attempted action.',
        ], 403),
    ]);

    $service = new DigitalOceanServerService('token');

    $service->find('web-1');
})->throws(RuntimeException::class, 'You do not have access for the attempted action.');

test('find returns null when not found', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets*' => Http::response(['droplets' => []]),
    ]);

    $service = new DigitalOceanServerService('token');

    expect($service->find('nonexistent'))->toBeNull();
});

test('destroy returns true on success', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets/*' => Http::response([], 204),
    ]);

    $service = new DigitalOceanServerService('token');

    expect($service->destroy(100))->toBeTrue();
});
