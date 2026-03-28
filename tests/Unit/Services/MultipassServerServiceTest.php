<?php

declare(strict_types=1);

use App\Data\CreateServerData;
use App\Enums\ServerStatus;
use App\Services\MultipassServerService;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

function fakeMultipassProcess(array $responses): Closure
{
    $callIndex = 0;

    return function (array $command) use ($responses, &$callIndex): Process {
        $response = $responses[$callIndex] ?? ['success' => true, 'output' => ''];
        $callIndex++;

        if ($response['success']) {
            $process = Process::fromShellCommandline('echo '.escapeshellarg($response['output'] ?? ''));
        } else {
            $process = Process::fromShellCommandline('echo '.escapeshellarg($response['error'] ?? 'error').' >&2 && exit 1');
        }
        $process->run();

        return $process;
    };
}

test('get all returns collection of server data', function (): void {
    $json = json_encode([
        'list' => [
            [
                'name' => 'web-1-abc123',
                'state' => 'Running',
                'ipv4' => ['192.168.64.2'],
            ],
        ],
    ]);

    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => true, 'output' => $json],
    ]));

    $servers = $service->getAll();

    expect($servers)->toHaveCount(1)
        ->and($servers[0]->name)->toBe('web-1-abc123')
        ->and($servers[0]->status)->toBe(ServerStatus::Running)
        ->and($servers[0]->region)->toBe('local')
        ->and($servers[0]->ipv4)->toBe('192.168.64.2');
});

test('get all throws on failure', function (): void {
    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => false, 'error' => 'multipass not running'],
    ]));

    $service->getAll();
})->throws(RuntimeException::class, 'Failed to list Multipass VMs');

test('create launches vm and returns server data', function (): void {
    Str::createRandomStringsUsing(fn (): string => 'x1y2z3');

    $infoJson = json_encode([
        'info' => [
            'web-1-x1y2z3' => [
                'state' => 'Running',
                'ipv4' => ['192.168.64.5'],
            ],
        ],
    ]);

    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => true, 'output' => ''],           // launch
        ['success' => true, 'output' => $infoJson],     // info
    ]));

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

    expect($server->name)->toBe('web-1-x1y2z3')
        ->and($server->externalId)->toBe('web-1-x1y2z3')
        ->and($server->status)->toBe(ServerStatus::Running)
        ->and($server->ipv4)->toBe('192.168.64.5');
});

test('create throws on launch failure', function (): void {
    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => false, 'error' => 'launch failed'],
    ]));

    $service->create(new CreateServerData(
        name: 'web-1',
        type: 'custom',
        image: 'noble',
        region: 'local',
        infrastructure_id: 'infra-1',
    ));
})->throws(RuntimeException::class, 'Failed to create Multipass VM');

test('find returns server data when found', function (): void {
    $infoJson = json_encode([
        'info' => [
            'web-1-abc123' => [
                'state' => 'Stopped',
                'ipv4' => ['192.168.64.2'],
            ],
        ],
    ]);

    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => true, 'output' => $infoJson],
    ]));

    $server = $service->find('web-1-abc123');

    expect($server)->not->toBeNull()
        ->and($server->name)->toBe('web-1-abc123')
        ->and($server->status)->toBe(ServerStatus::Off);
});

test('find returns null when not found', function (): void {
    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => false],
    ]));

    expect($service->find('nonexistent'))->toBeNull();
});

test('find returns null when name not in info response', function (): void {
    $infoJson = json_encode([
        'info' => [
            'other-vm' => ['state' => 'Running', 'ipv4' => []],
        ],
    ]);

    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => true, 'output' => $infoJson],
    ]));

    expect($service->find('web-1-abc123'))->toBeNull();
});

test('destroy deletes and purges successfully', function (): void {
    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => true, 'output' => ''],  // delete
        ['success' => true, 'output' => ''],  // purge
    ]));

    expect($service->destroy('web-1-abc123'))->toBeTrue();
});

test('destroy returns false when delete fails', function (): void {
    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => false, 'error' => 'not found'],
    ]));

    expect($service->destroy('nonexistent'))->toBeFalse();
});

test('destroy returns false when purge fails', function (): void {
    $service = new MultipassServerService(fakeMultipassProcess([
        ['success' => true, 'output' => ''],   // delete ok
        ['success' => false, 'error' => 'purge failed'],  // purge fails
    ]));

    expect($service->destroy('web-1-abc123'))->toBeFalse();
});
