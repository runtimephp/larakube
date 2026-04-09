<?php

declare(strict_types=1);

use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use App\Enums\KubernetesVersion;
use App\Models\ManagementCluster;
use App\Models\PlatformRegion;
use App\Models\Provider;

test('creates from model',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Provider $provider */
        $provider = Provider::factory()->hetzner()->create();

        /** @var PlatformRegion $region */
        $region = PlatformRegion::factory()->for($provider)->create();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()
            ->for($provider)
            ->for($region, 'platformRegion')
            ->ready()
            ->create(['name' => 'kuven-mgmt-local'])
            ->fresh();

        $data = ManagementClusterData::fromModel($cluster);

        expect($data->id)->toBe($cluster->id)
            ->and($data->name)->toBe('kuven-mgmt-local')
            ->and($data->providerId)->toBe($provider->id)
            ->and($data->platformRegionId)->toBe($region->id)
            ->and($data->status)->toBe('ready')
            ->and($data->version)->toBe(KubernetesVersion::V1_35_3->value);
    });

test('creates from array',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = ManagementClusterData::fromArray([
            'id' => 'uuid-123',
            'name' => 'kuven-mgmt-nuremberg',
            'provider_id' => 'provider-uuid',
            'platform_region_id' => 'region-uuid',
            'status' => 'bootstrapping',
            'version' => '1.35.3',
        ]);

        expect($data->id)->toBe('uuid-123')
            ->and($data->name)->toBe('kuven-mgmt-nuremberg')
            ->and($data->providerId)->toBe('provider-uuid')
            ->and($data->platformRegionId)->toBe('region-uuid')
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
            providerId: 'provider-uuid',
            platformRegionId: 'region-uuid',
            status: 'ready',
            version: '1.35.3',
        );

        expect($data->toArray())->toBe([
            'id' => 'uuid-123',
            'name' => 'kuven-mgmt-local',
            'provider_id' => 'provider-uuid',
            'platform_region_id' => 'region-uuid',
            'status' => 'ready',
            'version' => '1.35.3',
        ]);
    });

test('create management cluster data converts to array',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            providerId: 'provider-uuid',
            platformRegionId: 'region-uuid',
            version: KubernetesVersion::V1_35_3,
        );

        expect($data->toArray())->toBe([
            'name' => 'kuven-mgmt-local',
            'provider_id' => 'provider-uuid',
            'region_id' => 'region-uuid',
            'version' => KubernetesVersion::V1_35_3,
        ]);
    });
