<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProviderSlug;
use App\Models\Provider;
use Illuminate\Database\Seeder;

final class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProviderSlug::cases() as $slug) {
            Provider::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $slug->label(),
                    'is_active' => in_array($slug, [ProviderSlug::Hetzner, ProviderSlug::Docker]),
                    'api_token' => null,
                ],
            );
        }
    }
}
