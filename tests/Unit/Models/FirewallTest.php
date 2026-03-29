<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Firewall;
use App\Models\FirewallRule;
use App\Models\Infrastructure;
use Carbon\CarbonImmutable;

test('has many rules',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Firewall $firewall */
        $firewall = Firewall::factory()->createQuietly();

        FirewallRule::factory()->createQuietly(['firewall_id' => $firewall->id]);
        FirewallRule::factory()->createQuietly(['firewall_id' => $firewall->id]);

        expect($firewall->rules)->toHaveCount(2)
            ->and($firewall->rules->first())->toBeInstanceOf(FirewallRule::class);
    });

test('creates firewall',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Firewall $firewall */
        $firewall = Firewall::factory()->create([
            'name' => 'web-firewall',
        ]);

        expect($firewall->name)->toBe('web-firewall')
            ->and($firewall->id)->toBeString()
            ->and($firewall->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Firewall $firewall */
        $firewall = Firewall::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($firewall->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($firewall->infrastructure->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Firewall $firewall */
        $firewall = Firewall::factory()->create([
            'status' => InfrastructureStatus::Healthy,
        ]);

        expect($firewall->id)->toBeString()
            ->and($firewall->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($firewall->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($firewall->status)->toBe(InfrastructureStatus::Healthy);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Firewall $firewall */
        $firewall = Firewall::factory()->create();

        expect($firewall->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Firewall $firewall */
        $firewall = Firewall::factory()
            ->create()
            ->refresh();

        expect(array_keys($firewall->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'infrastructure_id',
                'name',
                'external_firewall_id',
                'status',
            ]);
    });
