<?php

declare(strict_types=1);

use App\Actions\CreateFirewallForInfrastructure;
use App\Models\CloudProvider;
use App\Models\Firewall;
use App\Models\Infrastructure;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryCloudProviderFactory;
use App\Services\InMemory\InMemoryFirewallService;

test('returns early when firewall already exists',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'cloud_provider_id' => $provider->id,
        ]);

        Firewall::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $firewallService = new InMemoryFirewallService();
        $factory = new InMemoryCloudProviderFactory(firewallService: $firewallService);

        $action = new CreateFirewallForInfrastructure($factory);
        $action->handle($infrastructure);

        expect($firewallService->list())->toBeEmpty()
            ->and(Firewall::where('infrastructure_id', $infrastructure->id)->count())->toBe(1);
    });

test('creates firewall via provider and stores model',
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

        $firewallService = new InMemoryFirewallService();

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeFirewallService')
            ->with($provider->type, $provider->api_token)
            ->once()
            ->andReturn($firewallService);

        $action = new CreateFirewallForInfrastructure($factory);
        $action->handle($infrastructure);

        expect($firewallService->list())->toHaveCount(1);

        /** @var Firewall $firewall */
        $firewall = Firewall::where('infrastructure_id', $infrastructure->id)->first();

        expect($firewall)->not->toBeNull()
            ->and($firewall->name)->toContain($infrastructure->name)
            ->and($firewall->external_firewall_id)->not->toBeNull();
    });
