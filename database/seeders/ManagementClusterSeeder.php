<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\KubernetesVersion;
use App\Enums\ManagementClusterStatus;
use App\Enums\ProviderSlug;
use App\Models\ManagementCluster;
use App\Models\PlatformRegion;
use App\Models\Provider;
use Illuminate\Database\Seeder;

final class ManagementClusterSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Provider $hetzner */
        $hetzner = Provider::query()->where('slug', ProviderSlug::Hetzner)->sole();

        /** @var PlatformRegion $region */
        $region = $hetzner->regions()->where('slug', 'fsn1')->sole();

        ManagementCluster::query()->firstOrCreate(
            ['name' => 'mgmt-production'],
            [
                'provider_id' => $hetzner->id,
                'platform_region_id' => $region->id,
                'status' => ManagementClusterStatus::Ready,
                'version' => KubernetesVersion::V1_35_3,
                'kubeconfig' => 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443',
            ],
        );

        /** @var Provider $digitalOcean */
        $digitalOcean = Provider::query()->where('slug', ProviderSlug::DigitalOcean)->sole();

        /** @var PlatformRegion $doRegion */
        $doRegion = $digitalOcean->regions()->where('slug', 'nyc1')->sole();

        ManagementCluster::query()->firstOrCreate(
            ['name' => 'mgmt-staging'],
            [
                'provider_id' => $digitalOcean->id,
                'platform_region_id' => $doRegion->id,
                'status' => ManagementClusterStatus::Ready,
                'version' => KubernetesVersion::V1_34_6,
                'kubeconfig' => 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443',
            ],
        );
    }
}
