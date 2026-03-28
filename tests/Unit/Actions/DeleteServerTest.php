<?php

declare(strict_types=1);

use App\Actions\DeleteServer;
use App\Models\CloudProvider;
use App\Models\Server;
use App\Services\InMemory\InMemoryHetznerFactory;
use App\Services\InMemory\InMemoryHetznerServerService;

test('delete server removes from api and locally',
    /**
     * @throws Throwable
     */
    function (): void {
        $provider = CloudProvider::factory()->hetzner()->create();
        $server = Server::factory()->create([
            'cloud_provider_id' => $provider->id,
            'organization_id' => $provider->organization_id,
        ]);

        $serverService = new InMemoryHetznerServerService();
        $serverService->addServer(new App\Data\ServerData(
            externalId: (string) $server->external_id,
            name: $server->name,
            status: $server->status,
            type: $server->type,
            region: $server->region,
            ipv4: $server->ipv4,
        ));

        $action = new DeleteServer(new InMemoryHetznerFactory(serverService: $serverService));
        $action->handle($server);

        $this->assertDatabaseMissing('servers', ['id' => $server->id]);
    });

test('delete server throws when api deletion fails',
    /**
     * @throws Throwable
     */
    function (): void {
        $provider = CloudProvider::factory()->hetzner()->create();
        $server = Server::factory()->create([
            'cloud_provider_id' => $provider->id,
            'organization_id' => $provider->organization_id,
            'name' => 'web-1',
        ]);

        $serverService = new InMemoryHetznerServerService();
        $serverService->shouldFailDelete(true);

        $action = new DeleteServer(new InMemoryHetznerFactory(serverService: $serverService));
        $action->handle($server);
    })->throws(RuntimeException::class, 'Failed to delete server [web-1] from the provider.');
