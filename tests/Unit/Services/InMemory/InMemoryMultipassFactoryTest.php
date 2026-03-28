<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Services\InMemory\InMemoryMultipassServerService;
use App\Services\InMemory\InMemoryMultipassService;

test('factory returns in-memory multipass validation service', function (): void {
    $validationService = useInMemoryMultipassService(true);
    bindInMemoryMultipassFactory($validationService);

    $factory = app(App\Services\CloudProviderFactory::class);
    $service = $factory->makeForValidation(CloudProviderType::Multipass);

    expect($service)
        ->toBeInstanceOf(InMemoryMultipassService::class)
        ->and($service->validateToken())->toBeTrue();
});

test('factory returns in-memory multipass server service', function (): void {
    $serverService = useInMemoryMultipassServerService();
    bindInMemoryMultipassFactory(serverService: $serverService);

    $factory = app(App\Services\CloudProviderFactory::class);
    $service = $factory->makeServerService(CloudProviderType::Multipass);

    expect($service)->toBeInstanceOf(InMemoryMultipassServerService::class);
});

test('factory falls back to parent for non-multipass server service', function (): void {
    bindInMemoryMultipassFactory();

    $factory = app(App\Services\CloudProviderFactory::class);
    $service = $factory->makeServerService(CloudProviderType::Hetzner, 'token');

    expect($service)->toBeInstanceOf(App\Services\HetznerServerService::class);
});

test('factory falls back to parent for non-multipass validation service', function (): void {
    bindInMemoryMultipassFactory();

    $factory = app(App\Services\CloudProviderFactory::class);
    $service = $factory->makeForValidation(CloudProviderType::Hetzner, 'token');

    expect($service)->toBeInstanceOf(App\Services\HetznerService::class);
});
