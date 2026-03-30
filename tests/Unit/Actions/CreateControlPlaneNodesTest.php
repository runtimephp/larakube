<?php

declare(strict_types=1);

use App\Actions\CreateControlPlaneNodes;
use App\Actions\CreateServer;
use App\Enums\CloudProviderType;
use App\Enums\ServerRole;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryHetznerServerService;

test('creates single control plane node for multipass',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly([
            'type' => CloudProviderType::Multipass,
        ]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'cloud_provider_id' => $provider->id,
            'organization_id' => $provider->organization_id,
        ]);

        SshKey::factory()->node()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_ssh_key_id' => '456',
        ]);

        $serverService = new InMemoryHetznerServerService();

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')
            ->with($provider->type, $provider->api_token)
            ->once()
            ->andReturn($serverService);

        $createServer = new CreateServer($factory);

        $action = new CreateControlPlaneNodes($createServer, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        $cpNodes = Server::where('infrastructure_id', $infrastructure->id)
            ->where('role', ServerRole::ControlPlane)
            ->get();

        expect($cpNodes)->toHaveCount(1)
            ->and($cpNodes[0]->name)->toContain('cp-1');
    });

test('creates three control plane nodes for hetzner',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly([
            'type' => CloudProviderType::Hetzner,
        ]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'cloud_provider_id' => $provider->id,
            'organization_id' => $provider->organization_id,
        ]);

        SshKey::factory()->node()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_ssh_key_id' => '456',
        ]);

        $serverService = new InMemoryHetznerServerService();

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')
            ->with($provider->type, $provider->api_token)
            ->times(3)
            ->andReturn($serverService);

        $createServer = new CreateServer($factory);

        $action = new CreateControlPlaneNodes($createServer, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        $cpNodes = Server::where('infrastructure_id', $infrastructure->id)
            ->where('role', ServerRole::ControlPlane)
            ->get();

        expect($cpNodes)->toHaveCount(3);
    });

test('skips if control plane nodes already exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::ControlPlane,
        ]);

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldNotReceive('makeServerService');

        $createServer = new CreateServer($factory);

        $action = new CreateControlPlaneNodes($createServer, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        $cpNodes = Server::where('infrastructure_id', $infrastructure->id)
            ->where('role', ServerRole::ControlPlane)
            ->get();

        expect($cpNodes)->toHaveCount(1);
    });
