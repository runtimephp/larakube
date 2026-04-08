<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProviderSlug;
use App\Models\Provider;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Seeder;

final class ProviderSeeder extends Seeder
{
    public function __construct(
        #[Config('cloud_providers.hetzner.token')] private readonly string $hetznerToken,
        #[Config('cloud_providers.do.token')] private readonly string $digitalOceanToken,
    ) {}

    public function run(): void
    {

        foreach ([ProviderSlug::Hetzner, ProviderSlug::DigitalOcean, ProviderSlug::Akamai, ProviderSlug::Docker] as $slug) {
            Provider::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $slug->label(),
                    'is_active' => in_array($slug, [ProviderSlug::Hetzner, ProviderSlug::Docker, ProviderSlug::DigitalOcean], true),
                    'api_token' => $slug === ProviderSlug::DigitalOcean ? $this->digitalOceanToken : $this->hetznerToken,
                ],
            );
        }
    }
}
