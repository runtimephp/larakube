<?php

declare(strict_types=1);

use App\Actions\CreateServer;
use App\Contracts\ServerManagerInterface;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;

test('create server persists locally after api call', function (): void {
    $provider = CloudProvider::factory()->hetzner()->create();

    $serverData = new ServerData(
        externalId: 12345,
        name: 'web-1',
        status: ServerStatus::Starting,
        type: 'cx11',
        region: 'fsn1',
        ipv4: '1.2.3.4',
        ipv6: null,
    );

    $mockManager = Mockery::mock(ServerManagerInterface::class);
    $mockManager->shouldReceive('create')
        ->once()
        ->andReturn($serverData);

    $action = new CreateServer($mockManager);
    $server = $action->handle(
        $provider,
        new CreateServerData(
            name: 'web-1',
            type: 'cx11',
            image: 'ubuntu-22.04',
            region: 'fsn1',
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
