<?php

namespace Database\Factories;

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CloudProvider>
 */
class CloudProviderFactory extends Factory
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
            'name' => $this->faker->company().' Cloud',
            'type' => $this->faker->randomElement(CloudProviderType::cases()),
            'api_token' => $this->faker->sha256(),
            'is_verified' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(['is_verified' => false]);
    }

    public function hetzner(): static
    {
        return $this->state(['type' => CloudProviderType::Hetzner]);
    }

    public function digitalOcean(): static
    {
        return $this->state(['type' => CloudProviderType::DigitalOcean]);
    }
}
