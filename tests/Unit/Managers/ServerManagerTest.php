<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use App\Managers\ServerManager;
use App\Models\CloudProvider;
use Illuminate\Support\Facades\Http;

test('list returns servers from provider api', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'servers' => [
                [
                    'id' => 1,
                    'name' => 'web-1',
                    'status' => 'running',
                    'server_type' => ['name' => 'cx11'],
                    'datacenter' => ['name' => 'fsn1'],
                    'public_net' => ['ipv4' => ['ip' => '1.2.3.4'], 'ipv6' => ['ip' => null]],
                ],
            ],
        ]),
    ]);

    $provider = CloudProvider::factory()->hetzner()->create();
    $manager = app(ServerManager::class);
    $servers = $manager->list($provider);

    expect($servers)->toHaveCount(1)
        ->and($servers[0])->toBeInstanceOf(ServerData::class)
        ->and($servers[0]->name)->toBe('web-1');
});

test('create creates server via provider api', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'server' => [
                'id' => 2,
                'name' => 'web-2',
                'status' => 'initializing',
                'server_type' => ['name' => 'cx21'],
                'datacenter' => ['name' => 'nbg1'],
                'public_net' => ['ipv4' => ['ip' => null], 'ipv6' => ['ip' => null]],
            ],
        ]),
    ]);

    $provider = CloudProvider::factory()->hetzner()->create();
    $manager = app(ServerManager::class);
    $server = $manager->create($provider, new CreateServerData(
        name: 'web-2',
        type: 'cx21',
        image: 'ubuntu-22.04',
        region: 'nbg1',
    ));

    expect($server->name)->toBe('web-2')
        ->and($server->status)->toBe(ServerStatus::Starting);
});

test('find by name returns server data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers*' => Http::response([
            'servers' => [
                [
                    'id' => 3,
                    'name' => 'web-1',
                    'status' => 'running',
                    'server_type' => ['name' => 'cx11'],
                    'datacenter' => ['name' => 'fsn1'],
                    'public_net' => ['ipv4' => ['ip' => '1.2.3.4'], 'ipv6' => ['ip' => null]],
                ],
            ],
        ]),
    ]);

    $provider = CloudProvider::factory()->hetzner()->create();
    $manager = app(ServerManager::class);

    expect($manager->findByName($provider, 'web-1'))->not->toBeNull();
});

test('delete returns true on success', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers/*' => Http::response([], 200),
    ]);

    $provider = CloudProvider::factory()->hetzner()->create();
    $manager = app(ServerManager::class);

    expect($manager->delete($provider, '123'))->toBeTrue();
});
