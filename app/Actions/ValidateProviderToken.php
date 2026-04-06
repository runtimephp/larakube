<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\CloudProviderService;
use App\Enums\ProviderSlug;
use App\Services\DigitalOceanService;
use App\Services\HetznerService;
use Illuminate\Validation\ValidationException;

final class ValidateProviderToken
{
    /**
     * @throws ValidationException
     */
    public function handle(ProviderSlug $slug, string $token): void
    {
        $service = $this->makeValidationService($slug, $token);

        if ($service === null) {
            return;
        }

        if (! $service->validateToken()) {
            throw ValidationException::withMessages([
                'api_token' => "The API token for {$slug->label()} is invalid.",
            ]);
        }
    }

    private function makeValidationService(ProviderSlug $slug, string $token): ?CloudProviderService
    {
        return match ($slug) {
            ProviderSlug::Hetzner => new HetznerService($token),
            ProviderSlug::DigitalOcean => new DigitalOceanService($token),
            default => null,
        };
    }
}
