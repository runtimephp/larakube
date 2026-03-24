<?php

declare(strict_types=1);

use App\Actions\DeleteServer;
use App\Contracts\ServerManagerInterface;
use App\Models\Server;

test('delete server removes from api and locally', function (): void {
    $server = Server::factory()->create();

    $mockManager = Mockery::mock(ServerManagerInterface::class);
    $mockManager->shouldReceive('delete')
        ->once()
        ->andReturnTrue();

    $action = new DeleteServer($mockManager);
    $action->handle($server);

    $this->assertDatabaseMissing('servers', ['id' => $server->id]);
});

test('delete server throws when api deletion fails', function (): void {
    $server = Server::factory()->create(['name' => 'web-1']);

    $mockManager = Mockery::mock(ServerManagerInterface::class);
    $mockManager->shouldReceive('delete')
        ->once()
        ->andReturnFalse();

    $action = new DeleteServer($mockManager);
    $action->handle($server);
})->throws(RuntimeException::class, 'Failed to delete server [web-1] from the provider.');
