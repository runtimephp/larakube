<?php

declare(strict_types=1);

use App\Actions\CreateServer;
use App\Data\CreateServerData;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Services\InMemory\InMemoryHetznerFactory;
use App\Services\InMemory\InMemoryHetznerServerService;

test('create server persists locally after api call',
    /**
     * @throws Throwable
     */
    function (): void {
        $provider = CloudProvider::factory()->hetzner()->create();
        $infrastructure = Infrastructure::factory()->create([
            'organization_id' => $provider->organization_id,
            'cloud_provider_id' => $provider->id,
        ]);

        $serverService = new InMemoryHetznerServerService();

        $action = new CreateServer(new InMemoryHetznerFactory(serverService: $serverService));
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
            ->and($server->external_id)->toBeString()
            ->and($server->status->label())->toBe('Running')
            ->and($server->cloud_provider_id)->toBe($provider->id)
            ->and($server->organization_id)->toBe($provider->organization_id);

        $this->assertDatabaseHas('servers', [
            'name' => 'web-1',
            'cloud_provider_id' => $provider->id,
        ]);
    });
