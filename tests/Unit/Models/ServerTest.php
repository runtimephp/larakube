<?php

declare(strict_types=1);

use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;
use App\Models\Organization;
use App\Models\Server;
use Carbon\CarbonImmutable;

test('creates server',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Server $server */
        $server = Server::factory()->create([
            'name' => 'web-1',
        ]);

        expect($server->name)->toBe('web-1')
            ->and($server->id)->toBeString()
            ->and($server->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'organization_id' => $organization->id,
        ]);

        expect($server->organization)->toBeInstanceOf(Organization::class)
            ->and($server->organization->id)->toBe($organization->id);
    });

test('belongs to cloud provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'cloud_provider_id' => $cloudProvider->id,
        ]);

        expect($server->cloudProvider)->toBeInstanceOf(CloudProvider::class)
            ->and($server->cloudProvider->id)->toBe($cloudProvider->id);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($server->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($server->infrastructure->id)->toBe($infrastructure->id);
    });

test('belongs to kubernetes cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'kubernetes_cluster_id' => $cluster->id,
        ]);

        expect($server->kubernetesCluster)->toBeInstanceOf(KubernetesCluster::class)
            ->and($server->kubernetesCluster->id)->toBe($cluster->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Server $server */
        $server = Server::factory()->running()->create();

        expect($server->id)->toBeString()
            ->and($server->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($server->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($server->status)->toBe(ServerStatus::Running);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Server $server */
        $server = Server::factory()->create();

        expect($server->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Server $server */
        $server = Server::factory()
            ->create()
            ->refresh();

        expect(array_keys($server->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'organization_id',
                'cloud_provider_id',
                'external_id',
                'name',
                'status',
                'type',
                'region',
                'ipv4',
                'ipv6',
                'metadata',
                'infrastructure_id',
                'kubernetes_cluster_id',
                'role',
            ]);
    });
