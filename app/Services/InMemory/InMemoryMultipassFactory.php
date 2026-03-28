<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\CloudProviderService;
use App\Contracts\ServerService;
use App\Enums\CloudProviderType;
use App\Services\CloudProviderFactory;

final class InMemoryMultipassFactory extends CloudProviderFactory
{
    public function __construct(
        private readonly ?InMemoryMultipassService $validationService = null,
        private readonly ?InMemoryMultipassServerService $serverService = null,
    ) {}

    public function makeServerService(CloudProviderType $type, ?string $token = null): ServerService
    {
        if ($type === CloudProviderType::Multipass && $this->serverService) {
            return $this->serverService;
        }

        return parent::makeServerService($type, $token);
    }

    public function makeForValidation(CloudProviderType $type, ?string $token = null): CloudProviderService
    {
        if ($type === CloudProviderType::Multipass && $this->validationService) {
            return $this->validationService;
        }

        return parent::makeForValidation($type, $token);
    }
}
