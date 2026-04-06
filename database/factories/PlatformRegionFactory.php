<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformRegion;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlatformRegion> */
final class PlatformRegionFactory extends Factory
{
    protected $model = PlatformRegion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'name' => $this->faker->city(),
            'slug' => $this->faker->unique()->slug(2),
            'country' => $this->faker->countryCode(),
            'city' => $this->faker->city(),
            'is_available' => true,
            'metadata' => null,
        ];
    }

    public function unavailable(): self
    {
        return $this->state([
            'is_available' => false,
        ]);
    }
}
