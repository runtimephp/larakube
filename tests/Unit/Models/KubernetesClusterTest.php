<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;
use App\Models\Server;
use Carbon\CarbonImmutable;

test('creates kubernetes cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'name' => 'prod-cluster',
        ]);

        expect($cluster->name)->toBe('prod-cluster')
            ->and($cluster->id)->toBeString()
            ->and($cluster->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($cluster->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($cluster->infrastructure->id)->toBe($infrastructure->id);
    });

test('has many nodes',
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

        expect($cluster->nodes)->toHaveCount(1)
            ->and($cluster->nodes->first())->toBeInstanceOf(Server::class)
            ->and($cluster->nodes->first()->id)->toBe($server->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'status' => InfrastructureStatus::Healthy,
        ]);

        expect($cluster->id)->toBeString()
            ->and($cluster->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($cluster->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($cluster->status)->toBe(InfrastructureStatus::Healthy);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create();

        expect($cluster->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()
            ->create()
            ->refresh();

        expect(array_keys($cluster->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'infrastructure_id',
                'name',
                'version',
                'external_cluster_id',
                'status',
            ]);
    });
