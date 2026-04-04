<?php

declare(strict_types=1);

use App\Models\ManagementCluster;
use App\Models\User;

test('store creates a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.management-clusters.store'), [
                'name' => 'kuven-mgmt-local',
                'provider' => 'docker',
                'region' => 'local',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'provider', 'region', 'status'],
            ])
            ->assertJsonPath('data.name', 'kuven-mgmt-local')
            ->assertJsonPath('data.provider', 'docker')
            ->assertJsonPath('data.region', 'local')
            ->assertJsonPath('data.status', 'bootstrapping');

        $this->assertDatabaseHas('management_clusters', [
            'name' => 'kuven-mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
        ]);
    });

test('store validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.management-clusters.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'provider', 'region']);
    });

test('store requires authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->postJson(route('api.v1.management-clusters.store'), [
            'name' => 'kuven-mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
        ]);

        $response->assertUnauthorized();
    });

test('index lists management clusters filtered by provider and region',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        ManagementCluster::factory()->ready()->create([
            'name' => 'kuven-mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
        ]);

        ManagementCluster::factory()->create([
            'provider' => 'hetzner',
            'region' => 'nuremberg',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.index', [
                'provider' => 'docker',
                'region' => 'local',
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'kuven-mgmt-local')
            ->assertJsonPath('data.0.status', 'ready');
    });

test('index returns empty array when no clusters match',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.index', [
                'provider' => 'docker',
                'region' => 'nonexistent',
            ]));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

test('show returns a management cluster by id',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->ready()->create([
            'name' => 'kuven-mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.show', $cluster));

        $response->assertOk()
            ->assertJsonPath('data.id', $cluster->id)
            ->assertJsonPath('data.name', 'kuven-mgmt-local');
    });

test('destroy deletes a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.management-clusters.destroy', $cluster));

        $response->assertNoContent();

        $this->assertDatabaseMissing('management_clusters', ['id' => $cluster->id]);
    });

test('kubeconfig update stores encrypted kubeconfig',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

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
        $user = User::factory()->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.v1.management-clusters.ready', $cluster));

        $response->assertNoContent();

        $cluster->refresh();

        expect($cluster->status->value)->toBe('ready');
    });
