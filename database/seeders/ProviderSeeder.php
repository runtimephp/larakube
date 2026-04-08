<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\SyncProviderRegions;
use App\Enums\ProviderSlug;
use App\Models\PlatformRegion;
use App\Models\Provider;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Seeder;

final class ProviderSeeder extends Seeder
{
    public function __construct(
        #[Config('cloud_providers.hetzner.token')] private readonly ?string $hetznerToken,
        #[Config('cloud_providers.do.token')] private readonly ?string $digitalOceanToken,
    ) {}

    public function run(): void
    {
        $activeProviders = [ProviderSlug::Hetzner, ProviderSlug::DigitalOcean, ProviderSlug::Docker];

        foreach (ProviderSlug::cases() as $slug) {
            Provider::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $slug->label(),
                    'is_active' => in_array($slug, $activeProviders, true),
                    'api_token' => match ($slug) {
                        ProviderSlug::Hetzner => $this->hetznerToken,
                        ProviderSlug::DigitalOcean => $this->digitalOceanToken,
                        default => null,
                    },
                ],
            );
        }

        $this->seedHetznerRegions();
        $this->seedDigitalOceanRegions();
    }

    private function seedHetznerRegions(): void
    {
        /** @var Provider $hetzner */
        $hetzner = Provider::query()->where('slug', ProviderSlug::Hetzner)->sole();

        if ($hetzner->regions()->exists()) {
            return;
        }

        if ($this->hetznerToken !== null) {
            app(SyncProviderRegions::class)->handle($hetzner);

            return;
        }

        $this->createStaticHetznerRegions($hetzner);
    }

    private function seedDigitalOceanRegions(): void
    {
        /** @var Provider $digitalOcean */
        $digitalOcean = Provider::query()->where('slug', ProviderSlug::DigitalOcean)->sole();

        if ($digitalOcean->regions()->exists()) {
            return;
        }

        if ($this->digitalOceanToken !== null) {
            app(SyncProviderRegions::class)->handle($digitalOcean);

            return;
        }

        $this->createStaticDigitalOceanRegions($digitalOcean);
    }

    private function createStaticDigitalOceanRegions(Provider $digitalOcean): void
    {
        $regions = [
            ['slug' => 'nyc1', 'name' => 'New York 1', 'country' => 'US', 'city' => 'New York'],
            ['slug' => 'ams3', 'name' => 'Amsterdam 3', 'country' => 'NL', 'city' => 'Amsterdam'],
            ['slug' => 'sgp1', 'name' => 'Singapore 1', 'country' => 'SG', 'city' => 'Singapore'],
        ];

        foreach ($regions as $region) {
            PlatformRegion::query()->firstOrCreate(
                [
                    'provider_id' => $digitalOcean->id,
                    'slug' => $region['slug'],
                ],
                [
                    'name' => $region['name'],
                    'country' => $region['country'],
                    'city' => $region['city'],
                    'is_available' => true,
                ],
            );
        }
    }

    private function createStaticHetznerRegions(Provider $hetzner): void
    {
        $regions = [
            ['slug' => 'fsn1', 'name' => 'Falkenstein DC Park 1', 'country' => 'DE', 'city' => 'Falkenstein'],
            ['slug' => 'nbg1', 'name' => 'Nuremberg DC Park 1', 'country' => 'DE', 'city' => 'Nuremberg'],
            ['slug' => 'hel1', 'name' => 'Helsinki DC Park 1', 'country' => 'FI', 'city' => 'Helsinki'],
        ];

        foreach ($regions as $region) {
            PlatformRegion::query()->firstOrCreate(
                [
                    'provider_id' => $hetzner->id,
                    'slug' => $region['slug'],
                ],
                [
                    'name' => $region['name'],
                    'country' => $region['country'],
                    'city' => $region['city'],
                    'is_available' => true,
                ],
            );
        }
    }
}
