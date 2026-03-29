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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $portStart = $this->faker->numberBetween(1, 65535);
        $direction = $this->faker->randomElement(['in', 'out']);

        return [
            'firewall_id' => Firewall::factory(),
            'direction' => $direction,
            'protocol' => $this->faker->randomElement(['tcp', 'udp']),
            'port_start' => $portStart,
            'port_end' => $this->faker->numberBetween($portStart, 65535),
            'source_ips' => $direction === 'in' ? ['0.0.0.0/0'] : null,
            'destination_ips' => $direction === 'out' ? ['0.0.0.0/0'] : null,
        ];
    }
}
