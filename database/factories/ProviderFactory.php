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

    public function hetzner(): self
    {
        return $this->state([
            'name' => ProviderSlug::Hetzner->label(),
            'slug' => ProviderSlug::Hetzner,
        ]);
    }

    public function digitalOcean(): self
    {
        return $this->state([
            'name' => ProviderSlug::DigitalOcean->label(),
            'slug' => ProviderSlug::DigitalOcean,
        ]);
    }

    public function akamai(): self
    {
        return $this->state([
            'name' => ProviderSlug::Akamai->label(),
            'slug' => ProviderSlug::Akamai,
        ]);
    }

    public function aws(): self
    {
        return $this->state([
            'name' => ProviderSlug::Aws->label(),
            'slug' => ProviderSlug::Aws,
        ]);
    }

    public function vultr(): self
    {
        return $this->state([
            'name' => ProviderSlug::Vultr->label(),
            'slug' => ProviderSlug::Vultr,
        ]);
    }

    public function docker(): self
    {
        return $this->state([
            'name' => ProviderSlug::Docker->label(),
            'slug' => ProviderSlug::Docker,
        ]);
    }
}
