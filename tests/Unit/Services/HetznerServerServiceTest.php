<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Enums\ServerStatus;
use App\Services\HetznerServerService;
use Illuminate\Support\Facades\Http;

test('create sends cloud init user data when provided', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'server' => [
                'id' => 789,
                'name' => 'bastion-1',
                'status' => 'initializing',
                'server_type' => ['name' => 'cx22'],
                'datacenter' => ['name' => 'hel1-dc2'],
                'public_net' => ['ipv4' => ['ip' => '1.2.3.4'], 'ipv6' => ['ip' => null]],
            ],
        ]),
    ]);

    $service = new HetznerServerService('token');
    $service->create(new CreateServerData(
        name: 'bastion-1',
        type: 'cx22',
        image: 'ubuntu-24.04',
        region: 'hel1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        cloudInit: '#cloud-config\npackages:\n  - curl',
    ));

    Http::assertSent(fn ($request) => $request->data()['user_data'] === '#cloud-config\npackages:\n  - curl');
});

test('create disables public net when public ip is false', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'server' => [
                'id' => 789,
                'name' => 'worker-1',
                'status' => 'initializing',
                'server_type' => ['name' => 'cpx32'],
                'datacenter' => ['name' => 'hel1-dc2'],
                'public_net' => ['ipv4' => ['ip' => null], 'ipv6' => ['ip' => null]],
                'private_net' => [['ip' => '10.0.0.5']],
            ],
        ]),
    ]);

    $service = new HetznerServerService('token');
    $service->create(new CreateServerData(
        name: 'worker-1',
        type: 'cpx32',
        image: 'ubuntu-24.04',
        region: 'hel1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        publicIp: false,
    ));

    Http::assertSent(function ($request) {
        $data = $request->data();

        return isset($data['public_net'])
            && $data['public_net']['enable_ipv4'] === false
            && $data['public_net']['enable_ipv6'] === false;
    });
});

test('create sends network id when provided', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'server' => [
                'id' => 789,
                'name' => 'worker-1',
                'status' => 'initializing',
                'server_type' => ['name' => 'cpx32'],
                'datacenter' => ['name' => 'hel1-dc2'],
                'public_net' => ['ipv4' => ['ip' => '1.2.3.4'], 'ipv6' => ['ip' => null]],
            ],
        ]),
    ]);

    $service = new HetznerServerService('token');
    $service->create(new CreateServerData(
        name: 'worker-1',
        type: 'cpx32',
        image: 'ubuntu-24.04',
        region: 'hel1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        networkId: '456',
    ));

    Http::assertSent(fn ($request) => $request->data()['networks'] === [456]);
});

test('get all returns collection of server data', function (): void {
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

    $service = new HetznerServerService('token');
    $servers = $service->getAll();

    expect($servers)->toHaveCount(1)
        ->and($servers[0]->externalId)->toBe(123)
        ->and($servers[0]->name)->toBe('web-1')
        ->and($servers[0]->status)->toBe(ServerStatus::Running)
        ->and($servers[0]->type)->toBe('cx11')
        ->and($servers[0]->region)->toBe('fsn1-dc14')
        ->and($servers[0]->ipv4)->toBe('1.2.3.4')
        ->and($servers[0]->ipv6)->toBe('2001:db8::1');
});

test('create sends ssh key ids when provided', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'server' => [
                'id' => 789,
                'name' => 'bastion-1',
                'status' => 'initializing',
                'server_type' => ['name' => 'cx22'],
                'datacenter' => ['name' => 'hel1-dc2'],
                'public_net' => [
                    'ipv4' => ['ip' => '1.2.3.4'],
                    'ipv6' => ['ip' => null],
                ],
            ],
        ]),
    ]);

    $service = new HetznerServerService('token');
    $service->create(new CreateServerData(
        name: 'bastion-1',
        type: 'cx22',
        image: 'ubuntu-24.04',
        region: 'hel1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        sshKeyIds: [123, 456],
    ));

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $data['ssh_keys'] === [123, 456];
    });
});

test('create does not send ssh keys when not provided', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'server' => [
                'id' => 789,
                'name' => 'web-1',
                'status' => 'initializing',
                'server_type' => ['name' => 'cx22'],
                'datacenter' => ['name' => 'hel1-dc2'],
                'public_net' => [
                    'ipv4' => ['ip' => '1.2.3.4'],
                    'ipv6' => ['ip' => null],
                ],
            ],
        ]),
    ]);

    $service = new HetznerServerService('token');
    $service->create(new CreateServerData(
        name: 'web-1',
        type: 'cx22',
        image: 'ubuntu-24.04',
        region: 'hel1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));

    Http::assertSent(fn ($request) => ! array_key_exists('ssh_keys', $request->data()));
});

test('create returns server data', function (): void {
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

    $service = new HetznerServerService('token');
    $server = $service->create(new CreateServerData(
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

test('create throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'error' => [
                'message' => 'server type 104 is deprecated',
                'code' => 'invalid_input',
            ],
        ], 422),
    ]);

    $service = new HetznerServerService('token');

    $service->create(new CreateServerData(
        name: 'web-1',
        type: 'cx22',
        image: 'ubuntu-22.04',
        region: 'fsn1',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));
})->throws(RuntimeException::class, 'server type 104 is deprecated');

test('get all throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers' => Http::response([
            'error' => ['message' => 'unauthorized', 'code' => 'unauthorized'],
        ], 401),
    ]);

    $service = new HetznerServerService('token');

    $service->getAll();
})->throws(RuntimeException::class, 'unauthorized');

test('find returns server data', function (): void {
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

    $service = new HetznerServerService('token');
    $server = $service->find('web-1');

    expect($server)->not->toBeNull()
        ->and($server->name)->toBe('web-1');
});

test('find throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers*' => Http::response([
            'error' => ['message' => 'forbidden', 'code' => 'forbidden'],
        ], 403),
    ]);

    $service = new HetznerServerService('token');

    $service->find('web-1');
})->throws(RuntimeException::class, 'forbidden');

test('find returns null when not found', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers*' => Http::response(['servers' => []]),
    ]);

    $service = new HetznerServerService('token');

    expect($service->find('nonexistent'))->toBeNull();
});

test('destroy returns true on success', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers/*' => Http::response([], 200),
    ]);

    $service = new HetznerServerService('token');

    expect($service->destroy(123))->toBeTrue();
});

test('destroy returns false on failure', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/servers/*' => Http::response([], 404),
    ]);

    $service = new HetznerServerService('token');

    expect($service->destroy(999))->toBeFalse();
});
