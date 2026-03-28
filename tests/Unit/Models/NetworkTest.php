<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\Network;
use Carbon\CarbonImmutable;

test('creates network',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Network $network */
        $network = Network::factory()->create([
            'name' => 'internal-net',
        ]);

        expect($network->name)->toBe('internal-net')
            ->and($network->id)->toBeString()
            ->and($network->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Network $network */
        $network = Network::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($network->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($network->infrastructure->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Network $network */
        $network = Network::factory()->create([
            'status' => InfrastructureStatus::Healthy,
        ]);

        expect($network->id)->toBeString()
            ->and($network->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($network->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($network->status)->toBe(InfrastructureStatus::Healthy);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Network $network */
        $network = Network::factory()->create();

        expect($network->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Network $network */
        $network = Network::factory()
            ->create()
            ->refresh();

        expect(array_keys($network->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'infrastructure_id',
                'name',
                'external_network_id',
                'cidr',
                'status',
            ]);
    });
