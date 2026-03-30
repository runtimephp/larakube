<?php

declare(strict_types=1);

use App\Actions\CreateServer;
use App\Actions\CreateWorkerNodes;
use App\Enums\ServerRole;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryHetznerServerService;

test('creates two worker nodes by default',
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

        SshKey::factory()->node()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_ssh_key_id' => '456',
        ]);

        $serverService = new InMemoryHetznerServerService();

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')
            ->with($provider->type, $provider->api_token)
            ->times(2)
            ->andReturn($serverService);

        $createServer = new CreateServer($factory);

        $action = new CreateWorkerNodes($createServer, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        $workers = Server::where('infrastructure_id', $infrastructure->id)
            ->where('role', ServerRole::Node)
            ->get();

        expect($workers)->toHaveCount(2)
            ->and($workers[0]->name)->toContain('worker-1')
            ->and($workers[1]->name)->toContain('worker-2');
    });

test('skips if worker nodes already exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Node,
        ]);

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldNotReceive('makeServerService');

        $createServer = new CreateServer($factory);

        $action = new CreateWorkerNodes($createServer, new ServerQuery(), new SshKeyQuery());
        $action->handle($infrastructure);

        $workers = Server::where('infrastructure_id', $infrastructure->id)
            ->where('role', ServerRole::Node)
            ->get();

        expect($workers)->toHaveCount(1);
    });
