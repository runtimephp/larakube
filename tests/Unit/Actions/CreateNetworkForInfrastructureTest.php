<?php

declare(strict_types=1);

use App\Actions\CreateNetworkForInfrastructure;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Network;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryNetworkService;

test('creates network via provider and stores model',
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

        $networkService = new InMemoryNetworkService();

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeNetworkService')
            ->with($provider->type, $provider->api_token)
            ->once()
            ->andReturn($networkService);

        $action = new CreateNetworkForInfrastructure($factory);
        $action->handle($infrastructure);

        expect($networkService->list())->toHaveCount(1);

        /** @var Network $network */
        $network = Network::where('infrastructure_id', $infrastructure->id)->first();

        expect($network)->not->toBeNull()
            ->and($network->name)->toContain($infrastructure->name)
            ->and($network->cidr)->toBe('10.0.0.0/16')
            ->and($network->external_network_id)->not->toBeNull();
    });
