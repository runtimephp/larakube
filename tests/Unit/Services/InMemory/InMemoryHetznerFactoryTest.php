<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Services\InMemory\InMemoryHetznerFactory;
use App\Services\InMemory\InMemoryHetznerServerService;
use App\Services\InMemory\InMemoryHetznerService;

beforeEach(function (): void {
    /** @var InMemoryHetznerService $this->validationService */
    $this->validationService = new InMemoryHetznerService();

    /** @var InMemoryHetznerServerService $this->serverService */
    $this->serverService = new InMemoryHetznerServerService();
});

test('makeServerService returns InMemoryHetznerServerService', function (): void {
    /** @var InMemoryHetznerFactory $factory */
    $factory = new InMemoryHetznerFactory(serverService: $this->serverService);

    $result = $factory->makeServerService(CloudProviderType::Hetzner, 'test-token');

    expect($result)->toBe($this->serverService);
});

test('makeForValidation returns InMemoryHetznerService', function (): void {
    /** @var InMemoryHetznerFactory $factory */
    $factory = new InMemoryHetznerFactory(validationService: $this->validationService);

    $result = $factory->makeForValidation(CloudProviderType::Hetzner, 'test-token');

    expect($result)->toBe($this->validationService);
});

test('makeServerService falls back to parent when service not provided', function (): void {
    /** @var InMemoryHetznerFactory $factory */
    $factory = new InMemoryHetznerFactory();

    $result = $factory->makeServerService(CloudProviderType::DigitalOcean, 'test-token');

    expect($result)->toBeInstanceOf(App\Services\DigitalOceanServerService::class);
});

test('makeForValidation falls back to parent when service not provided', function (): void {
    /** @var InMemoryHetznerFactory $factory */
    $factory = new InMemoryHetznerFactory();

    $result = $factory->makeForValidation(CloudProviderType::DigitalOcean, 'test-token');

    expect($result)->toBeInstanceOf(App\Services\DigitalOceanService::class);
});
