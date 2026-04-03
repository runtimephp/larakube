<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ManagementClusterStatus;
use App\Models\ManagementCluster;
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
            'region' => $this->faker->unique()->lexify('region-????'),
            'provider' => $this->faker->randomElement(['docker', 'hetzner']),
            'status' => ManagementClusterStatus::Bootstrapping,
        ];
    }

    public function ready(): self
    {
        return $this->state([
            'status' => ManagementClusterStatus::Ready,
            'kubeconfig' => 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443',
        ]);
    }
}
