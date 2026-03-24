<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\LoadBalancer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoadBalancer>
 */
final class LoadBalancerFactory extends Factory
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
            'name' => $this->faker->company().' Load Balancer',
            'external_load_balancer_id' => $this->faker->uuid(),
            'ip' => $this->faker->ipv4(),
            'status' => InfrastructureStatus::Healthy,
        ];
    }
}
