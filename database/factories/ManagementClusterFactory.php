<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KubernetesVersion;
use App\Enums\ManagementClusterStatus;
use App\Models\ManagementCluster;
use App\Models\PlatformRegion;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ManagementCluster> */
final class ManagementClusterFactory extends Factory
{
    protected $model = ManagementCluster::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => 'mgmt-'.$this->faker->word(),
            'provider_id' => null,
            'platform_region_id' => null,
            'status' => ManagementClusterStatus::Bootstrapping,
            'version' => fake()->randomElement([KubernetesVersion::V1_35_3, KubernetesVersion::V1_34_6]),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ManagementCluster $cluster) {
            if (! $cluster->provider_id && ! $cluster->platform_region_id) {
                $provider = Provider::factory()->create();
                $region = PlatformRegion::factory()->for($provider)->create();
                $cluster->provider_id = $provider->id;
                $cluster->platform_region_id = $region->id;
            }
        });
    }

    public function ready(): self
    {
        return $this->state([
            'status' => ManagementClusterStatus::Ready,
            'version' => KubernetesVersion::V1_35_3,
            'kubeconfig' => 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443',
        ]);
    }
}
