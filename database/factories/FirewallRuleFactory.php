<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Firewall;
use App\Models\FirewallRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FirewallRule>
 */
final class FirewallRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $portStart = $this->faker->numberBetween(1, 65535);

        return [
            'firewall_id' => Firewall::factory(),
            'direction' => $this->faker->randomElement(['in', 'out']),
            'protocol' => $this->faker->randomElement(['tcp', 'udp']),
            'port_start' => $portStart,
            'port_end' => $this->faker->numberBetween($portStart, 65535),
            'source_ips' => ['0.0.0.0/0'],
        ];
    }
}
