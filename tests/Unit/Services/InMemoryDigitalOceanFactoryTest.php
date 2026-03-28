<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Services\InMemory\InMemoryDigitalOceanFactory;
use App\Services\InMemory\InMemoryDigitalOceanServerService;
use App\Services\InMemory\InMemoryDigitalOceanService;

beforeEach(function (): void {
    /** @var InMemoryDigitalOceanService $this->validationService */
    $this->validationService = new InMemoryDigitalOceanService();

    /** @var InMemoryDigitalOceanServerService $this->serverService */
    $this->serverService = new InMemoryDigitalOceanServerService();
});

test('makeServerService returns InMemoryDigitalOceanServerService', function (): void {
    /** @var InMemoryDigitalOceanFactory $factory */
    $factory = new InMemoryDigitalOceanFactory(serverService: $this->serverService);

    $result = $factory->makeServerService(CloudProviderType::DigitalOcean, 'test-token');

    expect($result)->toBe($this->serverService);
});

test('makeForValidation returns InMemoryDigitalOceanService', function (): void {
    /** @var InMemoryDigitalOceanFactory $factory */
    $factory = new InMemoryDigitalOceanFactory(validationService: $this->validationService);

    $result = $factory->makeForValidation(CloudProviderType::DigitalOcean, 'test-token');

    expect($result)->toBe($this->validationService);
});

test('makeServerService falls back to parent when service not provided', function (): void {
    /** @var InMemoryDigitalOceanFactory $factory */
    $factory = new InMemoryDigitalOceanFactory();

    $result = $factory->makeServerService(CloudProviderType::Hetzner, 'test-token');

    expect($result)->toBeInstanceOf(App\Services\HetznerServerService::class);
});

test('makeForValidation falls back to parent when service not provided', function (): void {
    /** @var InMemoryDigitalOceanFactory $factory */
    $factory = new InMemoryDigitalOceanFactory();

    $result = $factory->makeForValidation(CloudProviderType::Hetzner, 'test-token');

    expect($result)->toBeInstanceOf(App\Services\HetznerService::class);
});
