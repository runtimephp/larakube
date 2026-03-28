<?php

declare(strict_types=1);

use App\Services\InMemory\InMemoryMultipassService;

test('validates token returns true by default', function (): void {
    $service = new InMemoryMultipassService();

    expect($service->validateToken())->toBeTrue();
});

test('validates token returns configured result', function (): void {
    $service = new InMemoryMultipassService();
    $service->setValidationResult(false);

    expect($service->validateToken())->toBeFalse();
});
