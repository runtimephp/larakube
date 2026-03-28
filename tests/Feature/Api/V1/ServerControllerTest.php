<?php

declare(strict_types=1);

use App\Data\ServerData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Server;
use App\Models\User;

beforeEach(function (): void {
    /** @var User $user */
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organizations()->attach($this->organization, ['role' => 'owner']);
    $this->provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $this->organization->id,
    ]);
    $this->infrastructure = Infrastructure::factory()->create([
        'organization_id' => $this->organization->id,
        'cloud_provider_id' => $this->provider->id,
    ]);

    $hetznerService = useInMemoryHetznerService(true);
    $this->serverService = useInMemoryHetznerServerService();
    bindInMemoryHetznerFactory($hetznerService, $this->serverService);
});

test('store creates a server and returns data',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders([
                'X-Organization-Id' => $this->organization->id,
                'X-Infrastructure-Id' => $this->infrastructure->id,
            ])
            ->postJson(route('api.v1.servers.store'), [
                'name' => 'web-1',
                'type' => 'cx11',
                'image' => 'ubuntu-22.04',
                'region' => 'fsn1',
                'cloud_provider_id' => $this->provider->id,
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'status', 'type', 'region'],
            ])
            ->assertJsonPath('data.name', 'web-1');

        $this->assertDatabaseHas('servers', [
            'name' => 'web-1',
            'cloud_provider_id' => $this->provider->id,
        ]);
    });

test('store validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders([
                'X-Organization-Id' => $this->organization->id,
                'X-Infrastructure-Id' => $this->infrastructure->id,
            ])
            ->postJson(route('api.v1.servers.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'type', 'image', 'region', 'cloud_provider_id']);
    });

test('index returns servers for organization',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'name' => 'web-1',
        ]);

        $otherOrg = Organization::factory()->create();
        Server::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'other-server',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->getJson(route('api.v1.servers.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'web-1');
    });

test('show returns server details',
    /**
     * @throws Throwable
     */
    function (): void {
        $server = Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'name' => 'web-1',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->getJson(route('api.v1.servers.show', $server));

        $response->assertOk()
            ->assertJsonPath('data.name', 'web-1')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'status', 'type', 'region', 'ipv4'],
            ]);
    });

test('destroy deletes a server',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->serverService->addServer(new ServerData(
            externalId: '123',
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
            ipv4: '1.2.3.4',
        ));

        $server = Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'name' => 'web-1',
            'external_id' => '123',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->deleteJson(route('api.v1.servers.destroy', $server));

        $response->assertNoContent();

        $this->assertDatabaseMissing('servers', ['id' => $server->id]);
    });

test('store fails when cloud provider api fails',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->serverService->shouldFailCreate(true);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders([
                'X-Organization-Id' => $this->organization->id,
                'X-Infrastructure-Id' => $this->infrastructure->id,
            ])
            ->postJson(route('api.v1.servers.store'), [
                'name' => 'web-1',
                'type' => 'cx11',
                'image' => 'ubuntu-22.04',
                'region' => 'fsn1',
                'cloud_provider_id' => $this->provider->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed');
    });

test('destroy fails when cloud provider api fails',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->serverService->shouldFailDelete(true);

        $server = Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'name' => 'web-1',
            'external_id' => '123',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->deleteJson(route('api.v1.servers.destroy', $server));

        $response->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed');
    });

test('all endpoints require authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->postJson(route('api.v1.servers.store'))->assertUnauthorized();
        $this->getJson(route('api.v1.servers.index'))->assertUnauthorized();
        $this->getJson(route('api.v1.servers.show', 'fake-id'))->assertUnauthorized();
        $this->deleteJson(route('api.v1.servers.destroy', 'fake-id'))->assertUnauthorized();
    });
