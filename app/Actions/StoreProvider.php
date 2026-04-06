<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ProviderSlug;
use App\Models\Provider;
use Illuminate\Validation\ValidationException;

final readonly class StoreProvider
{
    public function __construct(private ValidateProviderToken $validateToken) {}

    /**
     * @throws ValidationException
     */
    public function handle(ProviderSlug $slug, string $apiToken): Provider
    {
        if ($apiToken !== '') {
            $this->validateToken->handle($slug, $apiToken);
        }

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
