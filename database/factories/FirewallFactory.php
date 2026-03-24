<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InfrastructureStatus;
use App\Models\Firewall;
use App\Models\Infrastructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Firewall>
 */
final class FirewallFactory extends Factory
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
            'name' => $this->faker->company().' Firewall',
            'external_firewall_id' => $this->faker->uuid(),
            'status' => InfrastructureStatus::Healthy,
        ];
    }
}
