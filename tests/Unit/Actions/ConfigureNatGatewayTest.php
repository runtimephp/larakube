<?php

declare(strict_types=1);

use App\Actions\ConfigureNatGateway;
use App\Contracts\NatGatewayService;
use App\Data\NatGatewayConfigData;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Models\Infrastructure;
use App\Models\Network;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\InMemory\InMemoryCloudProviderFactory;
use App\Services\InMemory\InMemoryNatGatewayService;
use Illuminate\Support\Facades\Cache;

test('returns early when infrastructure has no network',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $natService = new InMemoryNatGatewayService();
        $factory = new InMemoryCloudProviderFactory(natGatewayService: $natService);

        $action = new ConfigureNatGateway($factory, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        expect($natService->configured)->toBeFalse();
    });

test('returns early when network has no external id',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Network::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_network_id' => null,
        ]);

        $natService = new InMemoryNatGatewayService();
        $factory = new InMemoryCloudProviderFactory(natGatewayService: $natService);

        $action = new ConfigureNatGateway($factory, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        expect($natService->configured)->toBeFalse();
    });

test('configures nat gateway and caches gateway ip',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Network::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_network_id' => 'net-123',
            'cidr' => '10.0.0.0/16',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
            'external_id' => 'srv-456',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $returnedGatewayIp = '10.0.0.1';

        $natService = new class($returnedGatewayIp) implements NatGatewayService
        {
            public bool $configured = false;

            public ?NatGatewayConfigData $receivedConfig = null;

            public function __construct(private readonly string $gatewayIp) {}

            public function configure(NatGatewayConfigData $config): ?string
            {
                $this->configured = true;
                $this->receivedConfig = $config;

                return $this->gatewayIp;
            }
        };

        $factory = new InMemoryCloudProviderFactory(natGatewayService: $natService);

        $action = new ConfigureNatGateway($factory, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        expect($natService->configured)->toBeTrue()
            ->and($natService->receivedConfig->networkId)->toBe('net-123')
            ->and($natService->receivedConfig->serverId)->toBe('srv-456')
            ->and($natService->receivedConfig->serverPublicIp)->toBe('192.168.1.1')
            ->and($natService->receivedConfig->networkCidr)->toBe('10.0.0.0/16')
            ->and(Cache::get("infrastructure.{$infrastructure->id}.gateway_ip"))->toBe('10.0.0.1');
    });

test('does not cache when gateway ip is null',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Network::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_network_id' => 'net-123',
            'cidr' => '10.0.0.0/16',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
            'external_id' => 'srv-456',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $natService = new InMemoryNatGatewayService();
        $factory = new InMemoryCloudProviderFactory(natGatewayService: $natService);

        $action = new ConfigureNatGateway($factory, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        expect($natService->configured)->toBeTrue()
            ->and(Cache::get("infrastructure.{$infrastructure->id}.gateway_ip"))->toBeNull();
    });

test('uses default cidr when network cidr is null',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Network::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_network_id' => 'net-123',
            'cidr' => null,
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
            'external_id' => 'srv-456',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $natService = new class implements NatGatewayService
        {
            public ?NatGatewayConfigData $receivedConfig = null;

            public function configure(NatGatewayConfigData $config): ?string
            {
                $this->receivedConfig = $config;

                return null;
            }
        };

        $factory = new InMemoryCloudProviderFactory(natGatewayService: $natService);

        $action = new ConfigureNatGateway($factory, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        expect($natService->receivedConfig->networkCidr)->toBe('10.0.0.0/16');
    });

test('getGatewayIp returns cached value',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Cache::put("infrastructure.{$infrastructure->id}.gateway_ip", '10.0.0.1', now()->addHour());

        expect(ConfigureNatGateway::getGatewayIp($infrastructure))->toBe('10.0.0.1');
    });

test('getGatewayIp returns null when not cached',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        expect(ConfigureNatGateway::getGatewayIp($infrastructure))->toBeNull();
    });
