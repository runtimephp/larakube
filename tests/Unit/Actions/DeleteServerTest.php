<?php

declare(strict_types=1);

use App\Actions\DeleteServer;
use App\Data\ServerData;
use App\Models\CloudProvider;
use App\Models\Server;

test('deletes server from api and locally',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'cloud_provider_id' => $provider->id,
            'organization_id' => $provider->organization_id,
        ]);

        $serverService = useInMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: (string) $server->external_id,
            name: $server->name,
            status: $server->status,
            type: $server->type,
            region: $server->region,
            ipv4: $server->ipv4,
        ));

        bindInMemoryHetznerFactory(serverService: $serverService);

        $action = app(DeleteServer::class);
        $action->handle($server);

        $this->assertDatabaseMissing('servers', ['id' => $server->id]);
    });

test('throws when api deletion fails',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'cloud_provider_id' => $provider->id,
            'organization_id' => $provider->organization_id,
            'name' => 'web-1',
        ]);

        $serverService = useInMemoryHetznerServerService();
        $serverService->shouldFailDelete(true);

        bindInMemoryHetznerFactory(serverService: $serverService);

        $action = app(DeleteServer::class);
        $action->handle($server);
    })->throws(RuntimeException::class, 'Failed to delete server [web-1] from the provider.');
