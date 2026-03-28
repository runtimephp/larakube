<?php

declare(strict_types=1);

use App\Services\InMemory\InMemoryDigitalOceanService;

beforeEach(function (): void {
    /** @var InMemoryDigitalOceanService $this->service */
    $this->service = new InMemoryDigitalOceanService();
});

test('validates token as valid', function (): void {
    $this->service->setValidationResult(true);

    expect($this->service->validateToken())->toBeTrue();
});

test('validates token as invalid', function (): void {
    $this->service->setValidationResult(false);

    expect($this->service->validateToken())->toBeFalse();
});

test('defaults to valid', function (): void {
    expect($this->service->validateToken())->toBeTrue();
});

test('set validation result returns self for fluent interface', function (): void {
    expect($this->service->setValidationResult(true))->toBeInstanceOf(InMemoryDigitalOceanService::class);
});
