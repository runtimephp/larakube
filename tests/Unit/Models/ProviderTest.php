<?php

declare(strict_types=1);

use App\Models\PlatformRegion;
use App\Models\Provider;
use Carbon\CarbonImmutable;

test('creates provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Provider $provider */
        $provider = Provider::factory()->active()->create([
            'name' => 'Hetzner',
        ]);

        expect($provider->name)->toBe('Hetzner')
            ->and($provider->id)->toBeString()
            ->and($provider->is_active)->toBeTrue()
            ->and($provider->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('has many regions',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Provider $provider */
        $provider = Provider::factory()->create();

        PlatformRegion::factory()->count(3)->create([
            'provider_id' => $provider->id,
        ]);

        expect($provider->regions)->toHaveCount(3)
            ->and($provider->regions->first())->toBeInstanceOf(PlatformRegion::class);
    });
