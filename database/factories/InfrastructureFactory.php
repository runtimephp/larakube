<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InfrastructureStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Infrastructure>
 */
final class InfrastructureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'cloud_provider_id' => CloudProvider::factory(),
            'region_id' => null,
            'name' => $this->faker->company().' Infrastructure',
            'description' => $this->faker->sentence(),
            'status' => InfrastructureStatus::Healthy,
        ];
    }

    public function provisioning(): static
    {
        return $this->state(['status' => InfrastructureStatus::Provisioning]);
    }

    public function degraded(): static
    {
        return $this->state(['status' => InfrastructureStatus::Degraded]);
    }

    public function failed(): static
    {
        return $this->state(['status' => InfrastructureStatus::Failed]);
    }
}
