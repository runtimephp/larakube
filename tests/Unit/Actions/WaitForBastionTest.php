<?php

declare(strict_types=1);

use App\Actions\ConfigureNatGateway;
use App\Actions\WaitForBastion;
use App\Data\ServerData;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Exceptions\RetryStepException;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\InMemory\InMemoryCloudProviderFactory;
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

        $factory = new InMemoryCloudProviderFactory(serverService: $serverService);
        $serverQuery = new ServerQuery();

        $action = new WaitForBastion($factory, new ConfigureNatGateway($factory, $serverQuery, new SshKeyQuery()), $serverQuery);
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

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $provider->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Starting,
            'name' => 'test-bastion',
        ]);

        $serverService = new InMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: 'ext-1',
            name: 'test-bastion',
            status: ServerStatus::Starting,
            type: 'cx22',
            region: 'hel1',
        ));

        $factory = new InMemoryCloudProviderFactory(serverService: $serverService);
        $serverQuery = new ServerQuery();

        $action = new WaitForBastion($factory, new ConfigureNatGateway($factory, $serverQuery, new SshKeyQuery()), $serverQuery);
        $action->handle($infrastructure);
    })->throws(RetryStepException::class);

test('throws when no bastion exists',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $factory = new InMemoryCloudProviderFactory();
        $serverQuery = new ServerQuery();

        $action = new WaitForBastion($factory, new ConfigureNatGateway($factory, $serverQuery, new SshKeyQuery()), $serverQuery);
        $action->handle($infrastructure);
    })->throws(RuntimeException::class, 'No bastion server found');
