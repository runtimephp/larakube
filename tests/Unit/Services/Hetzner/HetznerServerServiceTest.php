<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Enums\ServerStatus;
use App\Services\Hetzner\HetznerServerService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

test('get servers returns server data',
    /**
     * @throws ConnectionException
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers' => Http::response([
                'servers' => [
                    [
                        'id' => 123,
                        'name' => 'web-1',
                        'status' => 'running',
                        'server_type' => ['name' => 'cx11'],
                        'datacenter' => ['name' => 'fsn1-dc14'],
                        'public_net' => [
                            'ipv4' => ['ip' => '1.2.3.4'],
                            'ipv6' => ['ip' => '2001:db8::1'],
                        ],
                    ],
                ],
            ]),
        ]);

        $service = new HetznerServerService;
        $servers = $service->getServers('token');

        expect($servers)->toHaveCount(1)
            ->and($servers[0]->externalId)->toBe(123)
            ->and($servers[0]->name)->toBe('web-1')
            ->and($servers[0]->status)->toBe(ServerStatus::Running)
            ->and($servers[0]->type)->toBe('cx11')
            ->and($servers[0]->region)->toBe('fsn1-dc14')
            ->and($servers[0]->ipv4)->toBe('1.2.3.4')
            ->and($servers[0]->ipv6)->toBe('2001:db8::1');
    });

test('create server returns server data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'server' => [
                'id' => 456,
                'name' => 'web-2',
                'status' => 'initializing',
                'server_type' => ['name' => 'cx21'],
                'datacenter' => ['name' => 'nbg1-dc3'],
                'public_net' => [
                    'ipv4' => ['ip' => '5.6.7.8'],
                    'ipv6' => ['ip' => null],
                ],
            ],
        ]),
    ]);

    $service = new HetznerServerService;
    $server = $service->createServer('token', new CreateServerData(
        name: 'web-2',
        type: 'cx21',
        image: 'ubuntu-22.04',
        region: 'nbg1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));

    expect($server->externalId)->toBe(456)
        ->and($server->name)->toBe('web-2')
        ->and($server->status)->toBe(ServerStatus::Starting);
});

test('get server by name returns server data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers*' => Http::response([
            'servers' => [
                [
                    'id' => 789,
                    'name' => 'web-1',
                    'status' => 'running',
                    'server_type' => ['name' => 'cx11'],
                    'datacenter' => ['name' => 'fsn1-dc14'],
                    'public_net' => ['ipv4' => ['ip' => '1.2.3.4'], 'ipv6' => ['ip' => null]],
                ],
            ],
        ]),
    ]);

    $service = new HetznerServerService;
    $server = $service->getServerByName('token', 'web-1');

    expect($server)->not->toBeNull()
        ->and($server->name)->toBe('web-1');
});

test('get server by name returns null when not found', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers*' => Http::response(['servers' => []]),
    ]);

    $service = new HetznerServerService;

    expect($service->getServerByName('token', 'nonexistent'))->toBeNull();
});

test('delete server returns true on success', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers/*' => Http::response([], 200),
    ]);

    $service = new HetznerServerService;

    expect($service->deleteServer('token', 123))->toBeTrue();
});

test('delete server returns false on failure', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers/*' => Http::response([], 404),
    ]);

    $service = new HetznerServerService;

    expect($service->deleteServer('token', 999))->toBeFalse();
});
