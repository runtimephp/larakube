<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CloudProvider;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
final class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cloud_provider_id' => CloudProvider::factory(),
            'internal_name' => $this->faker->unique()->word(),
            'provider_region' => $this->faker->lexify('??-#'),
            'description' => $this->faker->sentence(),
        ];
    }
}
