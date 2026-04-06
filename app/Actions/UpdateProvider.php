<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Provider;

final readonly class UpdateProvider
{
    public function handle(Provider $provider, string $apiToken, bool $isActive): void
    {
        $data = ['is_active' => $isActive];

        if ($apiToken !== '') {
            $data['api_token'] = $apiToken;
        }

        $provider->update($data);
    }
}
