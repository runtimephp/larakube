<?php

declare(strict_types=1);

use App\Enums\ManagementClusterStatus;
use App\Models\ManagementCluster;
use Carbon\CarbonImmutable;

test('creates management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create([
            'name' => 'mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
        ]);

        expect($cluster->name)->toBe('mgmt-local')
            ->and($cluster->provider)->toBe('docker')
            ->and($cluster->region)->toBe('local')
            ->and($cluster->id)->toBeString()
            ->and($cluster->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect($cluster->id)
            ->toBeString()
            ->toBeUuid();
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->ready()->create();

        expect($cluster->id)->toBeString()
            ->and($cluster->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($cluster->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($cluster->status)->toBe(ManagementClusterStatus::Ready);
    });

test('encrypts kubeconfig at rest',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->ready()->create();

        $raw = DB::table('management_clusters')
            ->where('id', $cluster->id)
            ->value('kubeconfig');

        expect($raw)->not->toBe($cluster->kubeconfig)
            ->and($cluster->kubeconfig)->toContain('apiVersion');
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()
            ->create()
            ->refresh();

        expect(array_keys($cluster->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'name',
                'region',
                'provider',
                'kubeconfig',
                'status',
                'kubernetes_version',
                'ssh_private_key',
            ]);
    });
