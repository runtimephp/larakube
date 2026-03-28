<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use App\Services\InMemory\InMemoryDigitalOceanServerService;

beforeEach(function (): void {
    /** @var InMemoryDigitalOceanServerService $this->service */
    $this->service = new InMemoryDigitalOceanServerService();
});

test('returns empty collection when no servers', function (): void {
    expect($this->service->getAll())->toHaveCount(0);
});

test('returns all servers', function (): void {
    /** @var ServerData $server1 */
    $server1 = new ServerData(
        externalId: '1',
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    );

    /** @var ServerData $server2 */
    $server2 = new ServerData(
        externalId: '2',
        name: 'web-2',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.5',
    );

    $this->service->addServer($server1);
    $this->service->addServer($server2);

    expect($this->service->getAll())->toHaveCount(2);
});

test('find returns server by name', function (): void {
    /** @var ServerData $server */
    $server = new ServerData(
        externalId: '1',
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    );

    $this->service->addServer($server);

    expect($this->service->find('web-1'))->toBe($server);
});

test('find returns null when server not found', function (): void {
    expect($this->service->find('nonexistent'))->toBeNull();
});

test('create adds server and returns it', function (): void {
    $data = new CreateServerData(
        name: 'web-1',
        type: 'cx11',
        image: 'ubuntu-22.04',
        region: 'fsn1',
        infrastructure_id: 'infra-1',
    );

    /** @var ServerData $result */
    $result = $this->service->create($data);

    expect($result->name)->toBe('web-1')
        ->and($result->status)->toBe(ServerStatus::Running);

    expect($this->service->getAll())->toHaveCount(1);
});

test('create throws when shouldFailCreate is set', function (): void {
    $this->service->shouldFailCreate(true);

    $data = new CreateServerData(
        name: 'web-1',
        type: 'cx11',
        image: 'ubuntu-22.04',
        region: 'fsn1',
        infrastructure_id: 'infra-1',
    );

    $this->service->create($data);
})->throws(RuntimeException::class, 'Simulated API failure on create');

test('destroy removes server and returns true', function (): void {
    /** @var ServerData $server */
    $server = new ServerData(
        externalId: '123',
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    );

    $this->service->addServer($server);

    expect($this->service->destroy('123'))->toBeTrue();
    expect($this->service->getAll())->toHaveCount(0);
});

test('destroy returns false when server not found', function (): void {
    expect($this->service->destroy('nonexistent'))->toBeFalse();
});

test('destroy with string id marks server as deleted', function (): void {
    /** @var ServerData $server */
    $server = new ServerData(
        externalId: 'abc-123',
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    );

    $this->service->addServer($server);

    expect($this->service->destroy('abc-123'))->toBeTrue();
    expect($this->service->getAll())->toHaveCount(0);
});

test('destroy returns false when shouldFailDelete is set', function (): void {
    /** @var ServerData $server */
    $server = new ServerData(
        externalId: '123',
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    );

    $this->service->addServer($server);
    $this->service->shouldFailDelete(true);

    expect($this->service->destroy('123'))->toBeFalse();
});

test('set servers replaces all servers', function (): void {
    /** @var ServerData $server1 */
    $server1 = new ServerData(
        externalId: '1',
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    );

    /** @var ServerData $server2 */
    $server2 = new ServerData(
        externalId: '2',
        name: 'web-2',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.5',
    );

    $this->service->setServers([$server1, $server2]);

    expect($this->service->getAll())->toHaveCount(2);
});

test('add server returns self for fluent interface', function (): void {
    /** @var ServerData $server */
    $server = new ServerData(
        externalId: '1',
        name: 'web-1',
        status: ServerStatus::Running,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
    );

    expect($this->service->addServer($server))->toBeInstanceOf(InMemoryDigitalOceanServerService::class);
});

test('shouldFailCreate returns self for fluent interface', function (): void {
    expect($this->service->shouldFailCreate(true))->toBeInstanceOf(InMemoryDigitalOceanServerService::class);
});

test('shouldFailDelete returns self for fluent interface', function (): void {
    expect($this->service->shouldFailDelete(true))->toBeInstanceOf(InMemoryDigitalOceanServerService::class);
});
