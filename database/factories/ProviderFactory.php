<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProviderSlug;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Provider> */
final class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $slug = $this->faker->randomElement(ProviderSlug::cases());

        return [
            'name' => $slug->label(),
            'slug' => $slug,
            'is_active' => false,
            'api_token' => null,
        ];
    }

    public function active(): self
    {
        return $this->state([
            'is_active' => true,
        ]);
    }

    public function withApiToken(): self
    {
        return $this->state([
            'api_token' => 'test-api-token',
        ]);
    }
}
