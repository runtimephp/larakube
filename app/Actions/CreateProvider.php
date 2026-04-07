<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ProviderSlug;
use App\Models\Provider;

final readonly class CreateProvider
{
    public function handle(ProviderSlug $slug, string $apiToken): Provider
    {
        $data = [
            'name' => $slug->label(),
            'slug' => $slug,
            'is_active' => false,
        ];

        if ($apiToken !== '') {
            $data['api_token'] = $apiToken;
        }

        return Provider::query()->create($data);
    }
}
