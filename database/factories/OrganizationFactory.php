<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
final class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        $slug = str($name)->slug()->toString().'-'.$this->faker->unique()->numerify('########');

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->paragraph(),
        ];
    }
}
