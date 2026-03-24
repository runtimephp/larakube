<?php

declare(strict_types=1);

use App\Enums\ServerStatus;

test('from hetzner maps running', function (): void {
    expect(ServerStatus::fromHetzner('running'))->toBe(ServerStatus::Running);
});

test('from hetzner maps off', function (): void {
    expect(ServerStatus::fromHetzner('off'))->toBe(ServerStatus::Off);
});

test('from hetzner maps initializing to starting', function (): void {
    expect(ServerStatus::fromHetzner('initializing'))->toBe(ServerStatus::Starting);
});

test('from hetzner maps starting to starting', function (): void {
    expect(ServerStatus::fromHetzner('starting'))->toBe(ServerStatus::Starting);
});

test('from hetzner maps unknown status', function (): void {
    expect(ServerStatus::fromHetzner('rebuilding'))->toBe(ServerStatus::Unknown);
});

test('from digital ocean maps active to running', function (): void {
    expect(ServerStatus::fromDigitalOcean('active'))->toBe(ServerStatus::Running);
});

test('from digital ocean maps off', function (): void {
    expect(ServerStatus::fromDigitalOcean('off'))->toBe(ServerStatus::Off);
});

test('from digital ocean maps new to starting', function (): void {
    expect(ServerStatus::fromDigitalOcean('new'))->toBe(ServerStatus::Starting);
});

test('from digital ocean maps unknown status', function (): void {
    expect(ServerStatus::fromDigitalOcean('archive'))->toBe(ServerStatus::Unknown);
});

test('label returns correct labels', function (): void {
    expect(ServerStatus::Running->label())->toBe('Running')
        ->and(ServerStatus::Off->label())->toBe('Off')
        ->and(ServerStatus::Starting->label())->toBe('Starting')
        ->and(ServerStatus::Unknown->label())->toBe('Unknown');
});
