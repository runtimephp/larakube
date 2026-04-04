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

test('show finds management cluster by provider and region',
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

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.show', [
                'management_cluster' => 'lookup',
                'provider' => 'docker',
                'region' => 'local',
            ]));

        $response->assertOk()
            ->assertJsonPath('data.name', 'kuven-mgmt-local')
            ->assertJsonPath('data.status', 'ready');
    });

test('show returns 404 when cluster not found',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.management-clusters.show', [
                'management_cluster' => 'lookup',
                'provider' => 'docker',
                'region' => 'nonexistent',
            ]));

        $response->assertNotFound();
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
