<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use App\Services\InMemory\InMemoryMultipassServerService;

test('get all returns servers', function (): void {
    $service = new InMemoryMultipassServerService();
    $service->addServer(new ServerData(
        externalId: 'web-1-abc123',
        name: 'web-1-abc123',
        status: ServerStatus::Running,
        type: 'custom',
        region: 'local',
        ipv4: '192.168.64.2',
    ));

    $servers = $service->getAll();

    expect($servers)->toHaveCount(1)
        ->and($servers[0]->name)->toBe('web-1-abc123');
});

test('create returns server data with unique suffix', function (): void {
    $service = new InMemoryMultipassServerService();

    $server = $service->create(new CreateServerData(
        name: 'web-1',
        type: 'custom',
        image: 'noble',
        region: 'local',
        infrastructure_id: 'infra-1',
        cpus: 2,
        memory: '2G',
        disk: '20G',
    ));

    expect($server->name)->toStartWith('web-1-')
        ->and($server->name)->not->toBe('web-1')
        ->and($server->status)->toBe(ServerStatus::Running)
        ->and($server->region)->toBe('local');
});

test('create fails when configured to fail', function (): void {
    $service = new InMemoryMultipassServerService();
    $service->shouldFailCreate(true);

    $service->create(new CreateServerData(
        name: 'web-1',
        type: 'custom',
        image: 'noble',
        region: 'local',
        infrastructure_id: 'infra-1',
    ));
})->throws(RuntimeException::class);

test('find returns server by name', function (): void {
    $service = new InMemoryMultipassServerService();
    $service->addServer(new ServerData(
        externalId: 'web-1-abc123',
        name: 'web-1-abc123',
        status: ServerStatus::Running,
        type: 'custom',
        region: 'local',
    ));

    $server = $service->find('web-1-abc123');

    expect($server)->not->toBeNull()
        ->and($server->name)->toBe('web-1-abc123');
});

test('find returns null when not found', function (): void {
    $service = new InMemoryMultipassServerService();

    expect($service->find('nonexistent'))->toBeNull();
});

test('destroy returns true and marks server deleted', function (): void {
    $service = new InMemoryMultipassServerService();
    $service->addServer(new ServerData(
        externalId: 'web-1-abc123',
        name: 'web-1-abc123',
        status: ServerStatus::Running,
        type: 'custom',
        region: 'local',
    ));

    expect($service->destroy('web-1-abc123'))->toBeTrue()
        ->and($service->getAll())->toHaveCount(0);
});

test('destroy returns false when not found', function (): void {
    $service = new InMemoryMultipassServerService();

    expect($service->destroy('nonexistent'))->toBeFalse();
});

test('destroy fails when configured to fail', function (): void {
    $service = new InMemoryMultipassServerService();
    $service->shouldFailDelete(true);

    expect($service->destroy('web-1-abc123'))->toBeFalse();
});
