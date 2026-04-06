<?php

declare(strict_types=1);

use App\Models\PlatformRegion;
use App\Models\Provider;
use Carbon\CarbonImmutable;

test('creates platform region',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var PlatformRegion $region */
        $region = PlatformRegion::factory()->create([
            'name' => 'Falkenstein',
            'slug' => 'fsn1',
            'country' => 'DE',
            'city' => 'Falkenstein',
        ]);

        expect($region->name)->toBe('Falkenstein')
            ->and($region->slug)->toBe('fsn1')
            ->and($region->country)->toBe('DE')
            ->and($region->city)->toBe('Falkenstein')
            ->and($region->is_available)->toBeTrue()
            ->and($region->id)->toBeString()
            ->and($region->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Provider $provider */
        $provider = Provider::factory()->create();

        /** @var PlatformRegion $region */
        $region = PlatformRegion::factory()->create([
            'provider_id' => $provider->id,
        ]);

        expect($region->provider)->toBeInstanceOf(Provider::class)
            ->and($region->provider->id)->toBe($provider->id);
    });

test('unavailable state',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var PlatformRegion $region */
        $region = PlatformRegion::factory()->unavailable()->create();

        expect($region->is_available)->toBeFalse();
    });
