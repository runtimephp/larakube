<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Server>
 */
final class ServerFactory extends Factory
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
            'external_id' => (string) $this->faker->unique()->randomNumber(8),
            'name' => $this->faker->domainWord().'-server',
            'status' => ServerStatus::Running,
            'type' => $this->faker->randomElement(['cx11', 'cx21', 's-1vcpu-1gb', 's-2vcpu-2gb']),
            'region' => $this->faker->randomElement(['fsn1', 'nbg1', 'nyc1', 'sfo1']),
            'ipv4' => $this->faker->ipv4(),
            'ipv6' => null,
            'metadata' => null,
        ];
    }

    public function running(): static
    {
        return $this->state(['status' => ServerStatus::Running]);
    }

    public function off(): static
    {
        return $this->state(['status' => ServerStatus::Off]);
    }

    public function starting(): static
    {
        return $this->state(['status' => ServerStatus::Starting]);
    }
}
