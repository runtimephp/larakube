<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InfrastructureStatus;
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
        ];
    }
}
