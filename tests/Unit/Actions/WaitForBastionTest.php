<?php

declare(strict_types=1);

use App\Actions\WaitForBastion;
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

test('succeeds when bastion is running',
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

        /** @var Server $bastion */
        $bastion = Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $provider->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Starting,
            'name' => 'test-bastion',
        ]);

        $serverService = new InMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: $bastion->external_id,
            name: 'test-bastion',
            status: ServerStatus::Running,
            type: 'cx22',
            region: 'hel1',
            ipv4: '192.168.64.10',
        ));

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')
            ->with($provider->type, $provider->api_token)
            ->once()
            ->andReturn($serverService);

        $action = new WaitForBastion($factory, new ServerQuery());
        $action->handle($infrastructure);

        $bastion->refresh();

        expect($bastion->status)->toBe(ServerStatus::Running)
            ->and($bastion->ipv4)->toBe('192.168.64.10');
    });

test('throws retry exception when bastion is not running',
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

        /** @var Server $bastion */
        $bastion = Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $provider->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Starting,
            'name' => 'test-bastion',
        ]);

        $serverService = new InMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: $bastion->external_id,
            name: 'test-bastion',
            status: ServerStatus::Starting,
            type: 'cx22',
            region: 'hel1',
        ));

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')
            ->with($provider->type, $provider->api_token)
            ->once()
            ->andReturn($serverService);

        $action = new WaitForBastion($factory, new ServerQuery());
        $action->handle($infrastructure);
    })->throws(RetryStepException::class);

test('throws when no bastion exists',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $factory = Mockery::mock(CloudProviderFactory::class);

        $action = new WaitForBastion($factory, new ServerQuery());
        $action->handle($infrastructure);
    })->throws(RuntimeException::class, 'No bastion server found');
