<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\CloudProviderService;
use App\Contracts\ServerService;
use App\Enums\CloudProviderType;
use App\Services\CloudProviderFactory;

/**
 * Test factory that returns InMemory services for Hetzner.
 *
 * Use this in tests to avoid mocking CloudProviderFactory.
 */
final class InMemoryHetznerFactory extends CloudProviderFactory
{
    public function __construct(
        private readonly ?InMemoryHetznerService $validationService = null,
        private readonly ?InMemoryHetznerServerService $serverService = null,
    ) {}

    public function makeServerService(CloudProviderType $type, ?string $token = null): ServerService
    {
        if ($type === CloudProviderType::Hetzner && $this->serverService) {
            return $this->serverService;
        }

        return parent::makeServerService($type, $token);
    }

    public function makeForValidation(CloudProviderType $type, ?string $token = null): CloudProviderService
    {
        if ($type === CloudProviderType::Hetzner && $this->validationService) {
            return $this->validationService;
        }

        return parent::makeForValidation($type, $token);
    }
}
