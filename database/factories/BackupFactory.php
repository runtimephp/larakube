<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InfrastructureStatus;
use App\Models\Backup;
use App\Models\Infrastructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Backup>
 */
final class BackupFactory extends Factory
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
            'name' => $this->faker->company().' Backup',
            'external_backup_id' => $this->faker->uuid(),
            'status' => InfrastructureStatus::Healthy,
        ];
    }
}
