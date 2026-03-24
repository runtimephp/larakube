<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Infrastructure;
use App\Models\SshKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SshKey>
 */
final class SshKeyFactory extends Factory
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
            'name' => $this->faker->company().' SSH Key',
            'fingerprint' => $this->faker->sha256(),
            'public_key' => $this->faker->sha256(),
        ];
    }
}
