<?php

declare(strict_types=1);

use App\Actions\CreateServer;
use App\Contracts\ServerService;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Services\CloudProviderFactory;

test('create server persists locally after api call', function (): void {
    $provider = CloudProvider::factory()->hetzner()->create();
    $infrastructure = Infrastructure::factory()->create([
        'organization_id' => $provider->organization_id,
        'cloud_provider_id' => $provider->id,
    ]);

    $serverData = new ServerData(
        externalId: 12345,
        name: 'web-1',
        status: ServerStatus::Starting,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
        ipv6: null,
    );

    $mockServerService = Mockery::mock(ServerService::class);
    $mockServerService->shouldReceive('create')
        ->once()
        ->andReturn($serverData);

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeServerService')
        ->once()
        ->andReturn($mockServerService);

    $action = new CreateServer($mockFactory);
    $server = $action->handle(
        $provider,
        new CreateServerData(
            name: 'web-1',
            type: 'cx11',
            image: 'ubuntu-22.04',
            region: 'fsn1',
            infrastructure_id: $infrastructure->id,
        ),
    );

    expect($server->name)->toBe('web-1')
        ->and($server->external_id)->toBe('12345')
        ->and($server->status)->toBe(ServerStatus::Starting)
        ->and($server->cloud_provider_id)->toBe($provider->id)
        ->and($server->organization_id)->toBe($provider->organization_id);

    $this->assertDatabaseHas('servers', [
        'external_id' => '12345',
        'name' => 'web-1',
        'cloud_provider_id' => $provider->id,
    ]);
});
