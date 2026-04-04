<?php

declare(strict_types=1);

use App\Data\ManagementClusterData;
use App\Models\ManagementCluster;

test('creates from model',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->ready()->create([
            'name' => 'kuven-mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
        ]);

        $data = ManagementClusterData::fromModel($cluster);

        expect($data->id)->toBe($cluster->id)
            ->and($data->name)->toBe('kuven-mgmt-local')
            ->and($data->provider)->toBe('docker')
            ->and($data->region)->toBe('local')
            ->and($data->status)->toBe('ready');
    });

test('creates from array',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = ManagementClusterData::fromArray([
            'id' => 'uuid-123',
            'name' => 'kuven-mgmt-nuremberg',
            'provider' => 'hetzner',
            'region' => 'nuremberg',
            'status' => 'bootstrapping',
            'kubernetes_version' => 'v1.32.3',
        ]);

        expect($data->id)->toBe('uuid-123')
            ->and($data->name)->toBe('kuven-mgmt-nuremberg')
            ->and($data->provider)->toBe('hetzner')
            ->and($data->region)->toBe('nuremberg')
            ->and($data->status)->toBe('bootstrapping');
    });

test('converts to array',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new ManagementClusterData(
            id: 'uuid-123',
            name: 'kuven-mgmt-local',
            provider: 'docker',
            region: 'local',
            status: 'ready',
            kubernetesVersion: 'v1.32.3',
        );

        expect($data->toArray())->toBe([
            'id' => 'uuid-123',
            'name' => 'kuven-mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
            'status' => 'ready',
            'kubernetes_version' => 'v1.32.3',
        ]);
    });
