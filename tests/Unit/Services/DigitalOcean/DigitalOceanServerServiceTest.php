<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Enums\ServerStatus;
use App\Services\DigitalOcean\DigitalOceanServerService;
use Illuminate\Support\Facades\Http;

test('get servers returns server data', function (): void {
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

    $service = new DigitalOceanServerService;
    $servers = $service->getServers('token');

    expect($servers)->toHaveCount(1)
        ->and($servers[0]->externalId)->toBe(100)
        ->and($servers[0]->name)->toBe('web-1')
        ->and($servers[0]->status)->toBe(ServerStatus::Running)
        ->and($servers[0]->type)->toBe('s-1vcpu-1gb')
        ->and($servers[0]->region)->toBe('nyc1')
        ->and($servers[0]->ipv4)->toBe('1.2.3.4');
});

test('create server returns server data', function (): void {
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

    $service = new DigitalOceanServerService;
    $server = $service->createServer('token', new CreateServerData(
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

test('get server by name returns server data', function (): void {
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

    $service = new DigitalOceanServerService;
    $server = $service->getServerByName('token', 'web-1');

    expect($server)->not->toBeNull()
        ->and($server->name)->toBe('web-1');
});

test('get server by name returns null when not found', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets*' => Http::response(['droplets' => []]),
    ]);

    $service = new DigitalOceanServerService;

    expect($service->getServerByName('token', 'nonexistent'))->toBeNull();
});

test('delete server returns true on success', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/droplets/*' => Http::response([], 204),
    ]);

    $service = new DigitalOceanServerService;

    expect($service->deleteServer('token', 100))->toBeTrue();
});
