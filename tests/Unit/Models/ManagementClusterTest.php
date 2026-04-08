<?php

declare(strict_types=1);

use App\Data\KubernetesVersionData;
use App\Enums\KubernetesVersion;
use App\Enums\ManagementClusterStatus;
use App\Enums\ProviderSlug;
use App\Models\ManagementCluster;
use App\Models\PlatformRegion;
use App\Models\Provider;
use Carbon\CarbonImmutable;

test('creates management cluster',
    /**
     * @throws Throwable
     */
    function (): void {

        /** @var Provider $hetzner */
        $hetzner = Provider::factory()
            ->hetzner()
            ->create();

        /** @var PlatformRegion $platformRegion */
        $platformRegion = PlatformRegion::factory()
            ->for($hetzner)
            ->create();

        /** @var ManagementCluster $managementCluster */
        $managementCluster = ManagementCluster::factory()
            ->for($hetzner)
            ->for($platformRegion)
            ->create([
                'name' => 'mgmt-local',
                'version' => KubernetesVersion::V1_35_3,
            ])->fresh();

        expect($managementCluster->name)->toBe('mgmt-local')
            ->and($managementCluster->provider->slug)->toBe(ProviderSlug::Hetzner)
            ->and($managementCluster->platformRegion->id)->toBe($platformRegion->id)
            ->and($managementCluster->id)->toBeString()
            ->and($managementCluster->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($managementCluster->version)->toBeInstanceOf(KubernetesVersionData::class)
            ->and($managementCluster->version->name)->toBe('1.35.3');

    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $managementCluster */
        $managementCluster = ManagementCluster::factory()
            ->create();

        expect($managementCluster->id)
            ->toBeString()
            ->toBeUuid();
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()
            ->ready()
            ->create();

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
        /** @var ManagementCluster $managementCluster */
        $managementCluster = ManagementCluster::factory()
            ->ready()
            ->create();

        $raw = DB::table('management_clusters')
            ->where('id', $managementCluster->id)
            ->value('kubeconfig');

        expect($raw)->not->toBe($managementCluster->kubeconfig)
            ->and($managementCluster->kubeconfig)->toContain('apiVersion');
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $managementCluster */
        $managementCluster = ManagementCluster::factory()
            ->create()
            ->fresh();

        expect(array_keys($managementCluster->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'provider_id',
                'platform_region_id',
                'name',
                'kubeconfig',
                'status',
                'version',
            ]);
    });
