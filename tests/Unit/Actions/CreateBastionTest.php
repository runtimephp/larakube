<?php

declare(strict_types=1);

use App\Actions\CreateBastion;
use App\Actions\CreateServer;
use App\Enums\ServerRole;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudInitGenerator;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryHetznerServerService;

test('creates bastion server with cloud-init and ssh keys',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'cloud_provider_id' => $provider->id,
            'organization_id' => $provider->organization_id,
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_ssh_key_id' => '123',
        ]);

        $serverService = new InMemoryHetznerServerService();

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')
            ->with($provider->type, $provider->api_token)
            ->once()
            ->andReturn($serverService);

        $createServer = new CreateServer($factory);
        $cloudInit = new CloudInitGenerator();

        $action = new CreateBastion($createServer, $cloudInit, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        /** @var Server $bastion */
        $bastion = Server::where('infrastructure_id', $infrastructure->id)
            ->where('role', ServerRole::Bastion)
            ->first();

        expect($bastion)->not->toBeNull()
            ->and($bastion->name)->toContain('bastion')
            ->and($bastion->role)->toBe(ServerRole::Bastion)
            ->and($bastion->cloud_provider_id)->toBe($provider->id)
            ->and($bastion->organization_id)->toBe($provider->organization_id);
    });
