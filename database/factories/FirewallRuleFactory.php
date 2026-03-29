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
    public function definition(): array
    {
        return [
            'firewall_id' => Firewall::factory(),
            'direction' => $this->faker->randomElement(['in', 'out']),
            'protocol' => $this->faker->randomElement(['tcp', 'udp']),
            'port_start' => $this->faker->numberBetween(1, 65535),
            'port_end' => $this->faker->numberBetween(1, 65535),
            'source_ips' => ['0.0.0.0/0'],
        ];
    }
}
