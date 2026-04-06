<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Provider;
use Illuminate\Validation\ValidationException;

final readonly class UpdateProvider
{
    public function __construct(private ValidateProviderToken $validateToken) {}

    /**
     * @throws ValidationException
     */
    public function handle(Provider $provider, string $apiToken, bool $isActive): void
    {
        if ($apiToken !== '') {
            $this->validateToken->handle($provider->slug, $apiToken);
        }

        $data = ['is_active' => $isActive];

        if ($apiToken !== '') {
            $data['api_token'] = $apiToken;
        }

        $provider->update($data);
    }
}
