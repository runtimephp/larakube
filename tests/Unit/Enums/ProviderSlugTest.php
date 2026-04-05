<?php

declare(strict_types=1);

use App\Enums\ProviderSlug;

test('label returns correct labels', function (): void {
    expect(ProviderSlug::Hetzner->label())->toBe('Hetzner')
        ->and(ProviderSlug::DigitalOcean->label())->toBe('DigitalOcean')
        ->and(ProviderSlug::Aws->label())->toBe('AWS')
        ->and(ProviderSlug::Vultr->label())->toBe('Vultr')
        ->and(ProviderSlug::Akamai->label())->toBe('Akamai')
        ->and(ProviderSlug::Docker->label())->toBe('Docker');
});

test('cases returns all cases', function (): void {
    expect(ProviderSlug::cases())->toBe([
        ProviderSlug::Hetzner,
        ProviderSlug::DigitalOcean,
        ProviderSlug::Aws,
        ProviderSlug::Vultr,
        ProviderSlug::Akamai,
        ProviderSlug::Docker,
    ]);
});
