<?php

declare(strict_types=1);

use App\Actions\WaitForNodes;
use App\Data\ServerData;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Exceptions\RetryStepException;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Queries\ServerQuery;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryHetznerServerService;

test('succeeds when all nodes are running',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'cloud_provider_id' => $provider->id,
        ]);

        /** @var Server $cp */
        $cp = Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $provider->id,
            'role' => ServerRole::ControlPlane,
            'status' => ServerStatus::Starting,
            'name' => 'test-cp-1',
        ]);

        /** @var Server $worker */
        $worker = Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $provider->id,
            'role' => ServerRole::Node,
            'status' => ServerStatus::Starting,
            'name' => 'test-worker-1',
        ]);

        $serverService = new InMemoryHetznerServerService();
        $serverService->addServer(new ServerData(externalId: $cp->external_id, name: 'test-cp-1', status: ServerStatus::Running, type: 'cx32', region: 'hel1', ipv4: '10.0.1.1'));
        $serverService->addServer(new ServerData(externalId: $worker->external_id, name: 'test-worker-1', status: ServerStatus::Running, type: 'cx32', region: 'hel1', ipv4: '10.0.2.1'));

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')->andReturn($serverService);

        $action = new WaitForNodes($factory, new ServerQuery());
        $action->handle($infrastructure);

        $cp->refresh();
        $worker->refresh();

        expect($cp->status)->toBe(ServerStatus::Running)
            ->and($worker->status)->toBe(ServerStatus::Running);
    });

test('throws retry exception when any node is not running',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'cloud_provider_id' => $provider->id,
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $provider->id,
            'role' => ServerRole::ControlPlane,
            'status' => ServerStatus::Starting,
            'name' => 'test-cp-1',
        ]);

        $serverService = new InMemoryHetznerServerService();
        $serverService->addServer(new ServerData(externalId: 'ext-1', name: 'test-cp-1', status: ServerStatus::Starting, type: 'cx32', region: 'hel1'));

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')->andReturn($serverService);

        $action = new WaitForNodes($factory, new ServerQuery());
        $action->handle($infrastructure);
    })->throws(RetryStepException::class);

test('throws when no cluster nodes exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $factory = Mockery::mock(CloudProviderFactory::class);

        $action = new WaitForNodes($factory, new ServerQuery());
        $action->handle($infrastructure);
    })->throws(RuntimeException::class, 'No cluster nodes found');
