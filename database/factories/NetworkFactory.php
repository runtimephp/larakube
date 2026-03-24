<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\Network;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Network>
 */
final class NetworkFactory extends Factory
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
            'name' => $this->faker->company().' Network',
            'external_network_id' => $this->faker->uuid(),
            'cidr' => $this->faker->numerify('10.#.#.0/##'),
            'status' => InfrastructureStatus::Healthy,
        ];
    }
}
