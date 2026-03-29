<?php

declare(strict_types=1);

use App\Enums\ProvisioningPhase;

test('label returns correct labels', function (): void {
    expect(ProvisioningPhase::Infrastructure->label())->toBe('Infrastructure')
        ->and(ProvisioningPhase::Configuration->label())->toBe('Configuration');
});

test('cases returns all cases', function (): void {
    expect(ProvisioningPhase::cases())->toBe([
        ProvisioningPhase::Infrastructure,
        ProvisioningPhase::Configuration,
    ]);
});
