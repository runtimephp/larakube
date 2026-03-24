<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;

test('label returns correct labels', function (): void {
    expect(InfrastructureStatus::Provisioning->label())->toBe('Provisioning')
        ->and(InfrastructureStatus::Healthy->label())->toBe('Healthy')
        ->and(InfrastructureStatus::Degraded->label())->toBe('Degraded')
        ->and(InfrastructureStatus::Failed->label())->toBe('Failed');
});

test('from string returns correct status', function (): void {
    expect(InfrastructureStatus::from('provisioning'))->toBe(InfrastructureStatus::Provisioning)
        ->and(InfrastructureStatus::from('healthy'))->toBe(InfrastructureStatus::Healthy)
        ->and(InfrastructureStatus::from('degraded'))->toBe(InfrastructureStatus::Degraded)
        ->and(InfrastructureStatus::from('failed'))->toBe(InfrastructureStatus::Failed);
});

test('try from returns correct status', function (): void {
    expect(InfrastructureStatus::tryFrom('provisioning'))->toBe(InfrastructureStatus::Provisioning)
        ->and(InfrastructureStatus::tryFrom('healthy'))->toBe(InfrastructureStatus::Healthy)
        ->and(InfrastructureStatus::tryFrom('degraded'))->toBe(InfrastructureStatus::Degraded)
        ->and(InfrastructureStatus::tryFrom('failed'))->toBe(InfrastructureStatus::Failed)
        ->and(InfrastructureStatus::tryFrom('invalid'))->toBeNull();
});

test('cases returns all cases', function (): void {
    expect(InfrastructureStatus::cases())->toBe([
        InfrastructureStatus::Provisioning,
        InfrastructureStatus::Healthy,
        InfrastructureStatus::Degraded,
        InfrastructureStatus::Failed,
    ]);
});
