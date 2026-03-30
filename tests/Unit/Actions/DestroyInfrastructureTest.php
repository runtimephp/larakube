<?php

declare(strict_types=1);

use App\Actions\DestroyInfrastructure;
use App\Contracts\ServerService;
use App\Enums\InfrastructureStatus;
use App\Models\CloudProvider;
use App\Models\Firewall;
use App\Models\Infrastructure;
use App\Models\Network;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryCloudProviderFactory;
use App\Services\InMemory\InMemoryFirewallService;
use App\Services\InMemory\InMemoryHetznerServerService;
use App\Services\InMemory\InMemoryNetworkService;
use App\Services\InMemory\InMemorySshKeyService;

test('catches firewall delete failure and reports it',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'cloud_provider_id' => $provider->id,
            'status' => InfrastructureStatus::Failed,
        ]);

        Firewall::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_firewall_id' => '1',
        ]);

        $firewallService = new InMemoryFirewallService();
        $firewallService->shouldThrowOnDelete();

        $factory = new InMemoryCloudProviderFactory(
            serverService: new InMemoryHetznerServerService(),
            sshKeyService: new InMemorySshKeyService(),
            networkService: new InMemoryNetworkService(),
            firewallService: $firewallService,
        );

        $action = new DestroyInfrastructure($factory, new ServerQuery(), new SshKeyQuery());
        $failures = $action->handle($infrastructure);

        expect($failures)->not->toBeEmpty()
            ->and($failures[0])->toContain('firewall')
            ->and(Firewall::where('infrastructure_id', $infrastructure->id)->count())->toBe(0);
    });

test('catches network delete failure and reports it',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'cloud_provider_id' => $provider->id,
            'status' => InfrastructureStatus::Failed,
        ]);

        Network::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_network_id' => '1',
        ]);

        $networkService = new InMemoryNetworkService();
        $networkService->shouldThrowOnDelete();

        $factory = new InMemoryCloudProviderFactory(
            serverService: new InMemoryHetznerServerService(),
            sshKeyService: new InMemorySshKeyService(),
            networkService: $networkService,
            firewallService: new InMemoryFirewallService(),
        );

        $action = new DestroyInfrastructure($factory, new ServerQuery(), new SshKeyQuery());
        $failures = $action->handle($infrastructure);

        expect($failures)->not->toBeEmpty()
            ->and($failures[0])->toContain('network')
            ->and(Network::where('infrastructure_id', $infrastructure->id)->count())->toBe(0);
    });

test('catches ssh key delete failure and reports it',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'cloud_provider_id' => $provider->id,
            'status' => InfrastructureStatus::Failed,
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_ssh_key_id' => '1',
        ]);

        $sshKeyService = new InMemorySshKeyService();
        $sshKeyService->shouldThrowOnDelete();

        $factory = new InMemoryCloudProviderFactory(
            serverService: new InMemoryHetznerServerService(),
            sshKeyService: $sshKeyService,
            networkService: new InMemoryNetworkService(),
            firewallService: new InMemoryFirewallService(),
        );

        $action = new DestroyInfrastructure($factory, new ServerQuery(), new SshKeyQuery());
        $failures = $action->handle($infrastructure);

        expect($failures)->not->toBeEmpty()
            ->and($failures[0])->toContain('SSH key')
            ->and(SshKey::where('infrastructure_id', $infrastructure->id)->count())->toBe(0);
    });

test('destroys all resources and sets status to destroyed',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'cloud_provider_id' => $provider->id,
            'status' => InfrastructureStatus::Failed,
        ]);

        Server::factory()->createQuietly(['infrastructure_id' => $infrastructure->id]);
        SshKey::factory()->bastion()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_ssh_key_id' => '1']);
        Network::factory()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_network_id' => '1']);
        Firewall::factory()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_firewall_id' => '1']);

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')->andReturn(new InMemoryHetznerServerService());
        $factory->shouldReceive('makeSshKeyService')->andReturn(new InMemorySshKeyService());
        $factory->shouldReceive('makeNetworkService')->andReturn(new InMemoryNetworkService());
        $factory->shouldReceive('makeFirewallService')->andReturn(new InMemoryFirewallService());

        $action = new DestroyInfrastructure($factory, new ServerQuery(), new SshKeyQuery());
        $failures = $action->handle($infrastructure);

        expect($failures)->toBeEmpty()
            ->and(Server::where('infrastructure_id', $infrastructure->id)->count())->toBe(0)
            ->and(SshKey::where('infrastructure_id', $infrastructure->id)->count())->toBe(0)
            ->and(Network::where('infrastructure_id', $infrastructure->id)->count())->toBe(0)
            ->and(Firewall::where('infrastructure_id', $infrastructure->id)->count())->toBe(0);

        $infrastructure->refresh();
        expect($infrastructure->status)->toBe(InfrastructureStatus::Destroyed);
    });

test('returns failures when cloud api calls fail',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'cloud_provider_id' => $provider->id,
        ]);

        Server::factory()->createQuietly(['infrastructure_id' => $infrastructure->id]);

        $serverService = Mockery::mock(ServerService::class);
        $serverService->shouldReceive('destroy')->andThrow(new RuntimeException('API timeout'));

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')->andReturn($serverService);
        $factory->shouldReceive('makeSshKeyService')->andReturn(new InMemorySshKeyService());
        $factory->shouldReceive('makeNetworkService')->andReturn(new InMemoryNetworkService());
        $factory->shouldReceive('makeFirewallService')->andReturn(new InMemoryFirewallService());

        $action = new DestroyInfrastructure($factory, new ServerQuery(), new SshKeyQuery());
        $failures = $action->handle($infrastructure);

        expect($failures)->not->toBeEmpty()
            ->and(Server::where('infrastructure_id', $infrastructure->id)->count())->toBe(0);
    });
