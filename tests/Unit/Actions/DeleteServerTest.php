<?php

declare(strict_types=1);

use App\Actions\DeleteServer;
use App\Contracts\ServerService;
use App\Models\Server;
use App\Services\CloudProviderFactory;

test('delete server removes from api and locally', function (): void {
    $server = Server::factory()->create();

    $mockServerService = Mockery::mock(ServerService::class);
    $mockServerService->shouldReceive('destroy')
        ->once()
        ->andReturnTrue();

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeServerService')
        ->once()
        ->andReturn($mockServerService);

    $action = new DeleteServer($mockFactory);
    $action->handle($server);

    $this->assertDatabaseMissing('servers', ['id' => $server->id]);
});

test('delete server throws when api deletion fails', function (): void {
    $server = Server::factory()->create(['name' => 'web-1']);

    $mockServerService = Mockery::mock(ServerService::class);
    $mockServerService->shouldReceive('destroy')
        ->once()
        ->andReturnFalse();

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeServerService')
        ->once()
        ->andReturn($mockServerService);

    $action = new DeleteServer($mockFactory);
    $action->handle($server);
})->throws(RuntimeException::class, 'Failed to delete server [web-1] from the provider.');
