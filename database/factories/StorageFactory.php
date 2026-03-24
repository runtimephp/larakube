<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Storage>
 */
final class StorageFactory extends Factory
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
            'name' => $this->faker->company().' Storage',
            'external_volume_id' => $this->faker->uuid(),
            'size_gb' => $this->faker->numberBetween(10, 1000),
            'status' => InfrastructureStatus::Healthy,
        ];
    }
}
