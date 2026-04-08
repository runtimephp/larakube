<?php

declare(strict_types=1);

use App\Contracts\BootstrapClusterService;
use App\Enums\KubernetesVersion;
use App\Models\ManagementCluster;
use App\Models\PlatformRegion;
use App\Models\Provider;
use App\Models\User;
use App\Services\InMemory\InMemoryBootstrapClusterService;

beforeEach(function (): void {
    $this->bootstrapService = new InMemoryBootstrapClusterService;
    $this->app->instance(BootstrapClusterService::class, $this->bootstrapService);

    /** @var Provider $provider */
    $this->provider = Provider::factory()->hetzner()->create();

    /** @var PlatformRegion $region */
    $this->region = PlatformRegion::factory()->for($this->provider)->create(['slug' => 'fsn1']);
});

test('store creates a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.management-clusters.store'), [
                'name' => 'kuven-mgmt-local',
                'provider_id' => $this->provider->id,
                'platform_region_id' => $this->region->id,
                'version' => KubernetesVersion::V1_35_3->value,
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'provider' => ['id', 'slug'], 'region' => ['id', 'slug'], 'status', 'version' => ['name']],
            ])
            ->assertJsonPath('data.name', 'kuven-mgmt-local')
            ->assertJsonPath('data.provider.slug', 'hetzner')
            ->assertJsonPath('data.region.slug', 'fsn1')
            ->assertJsonPath('data.status', 'bootstrapping');

        $this->assertDatabaseHas('management_clusters', [
            'name' => 'kuven-mgmt-local',
            'provider_id' => $this->provider->id,
            'platform_region_id' => $this->region->id,
        ]);
    });

test('store validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.management-clusters.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'provider_id', 'platform_region_id', 'version']);
    });

test('store requires authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->postJson(route('api.v1.management-clusters.store'), [
            'name' => 'kuven-mgmt-local',
            'provider_id' => $this->provider->id,
            'platform_region_id' => $this->region->id,
            'version' => KubernetesVersion::V1_35_3->value,
        ]);

        $response->assertUnauthorized();
    });

test('non-admin cannot access management clusters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.management-clusters.store'), [
                'name' => 'kuven-mgmt-local',
                'provider_id' => $this->provider->id,
                'platform_region_id' => $this->region->id,
                'version' => KubernetesVersion::V1_35_3->value,
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.index'))
            ->assertForbidden();
    });

test('index lists management clusters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        ManagementCluster::factory()
            ->for($this->provider)
            ->for($this->region, 'platformRegion')
            ->ready()
            ->create(['name' => 'kuven-mgmt-local']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'kuven-mgmt-local')
            ->assertJsonPath('data.0.status', 'ready');
    });

test('index returns empty array when no clusters exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

test('show returns a management cluster by id',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()
            ->for($this->provider)
            ->for($this->region, 'platformRegion')
            ->ready()
            ->create(['name' => 'kuven-mgmt-local']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.show', $cluster));

        $response->assertOk()
            ->assertJsonPath('data.id', $cluster->id)
            ->assertJsonPath('data.name', 'kuven-mgmt-local');
    });

test('destroy deletes a management cluster and its bootstrap cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()
            ->for($this->provider)
            ->for($this->region, 'platformRegion')
            ->create(['name' => 'kuven-mgmt-local']);

        $this->bootstrapService->addCluster($cluster->name);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.management-clusters.destroy', $cluster));

        $response->assertNoContent();

        $this->assertDatabaseMissing('management_clusters', ['id' => $cluster->id]);
        expect($this->bootstrapService->exists('kuven-mgmt-local'))->toBeFalse();
    });

test('kubeconfig update stores encrypted kubeconfig',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()
            ->for($this->provider)
            ->for($this->region, 'platformRegion')
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.v1.management-clusters.kubeconfig', $cluster), [
                'kubeconfig' => 'apiVersion: v1\nclusters: []',
            ]);

        $response->assertNoContent();

        $cluster->refresh();

        expect($cluster->kubeconfig)->toBe('apiVersion: v1\nclusters: []');
    });

test('ready update marks cluster as ready',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()
            ->for($this->provider)
            ->for($this->region, 'platformRegion')
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.v1.management-clusters.ready', $cluster));

        $response->assertNoContent();

        $cluster->refresh();

        expect($cluster->status->value)->toBe('ready');
    });
