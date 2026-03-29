<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClusterTopology;
use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KubernetesCluster>
 */
final class KubernetesClusterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'infrastructure_id' => Infrastructure::factory(),
            'name' => $this->faker->company().' Cluster',
            'version' => $this->faker->randomElement(['1.28', '1.29', '1.30']),
            'external_cluster_id' => $this->faker->uuid(),
            'status' => InfrastructureStatus::Healthy,
            'topology' => $this->faker->randomElement(ClusterTopology::cases()),
            'pod_cidr' => '10.244.0.0/16',
            'service_cidr' => '10.96.0.0/12',
        ];
    }

    public function singleCp(): static
    {
        return $this->state(fn (): array => [
            'topology' => ClusterTopology::SingleCp,
        ]);
    }

    public function ha(): static
    {
        return $this->state(fn (): array => [
            'topology' => ClusterTopology::Ha,
        ]);
    }

    public function provisioning(): static
    {
        return $this->state(fn (): array => [
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_phase' => ProvisioningPhase::Infrastructure,
            'provisioning_step' => 'generate_ssh_keypairs',
        ]);
    }
}
