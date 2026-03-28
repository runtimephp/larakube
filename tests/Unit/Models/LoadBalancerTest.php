<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\LoadBalancer;
use Carbon\CarbonImmutable;

test('creates load balancer',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var LoadBalancer $loadBalancer */
        $loadBalancer = LoadBalancer::factory()->create([
            'name' => 'main-lb',
        ]);

        expect($loadBalancer->name)->toBe('main-lb')
            ->and($loadBalancer->id)->toBeString()
            ->and($loadBalancer->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var LoadBalancer $loadBalancer */
        $loadBalancer = LoadBalancer::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($loadBalancer->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($loadBalancer->infrastructure->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var LoadBalancer $loadBalancer */
        $loadBalancer = LoadBalancer::factory()->create([
            'status' => InfrastructureStatus::Healthy,
        ]);

        expect($loadBalancer->id)->toBeString()
            ->and($loadBalancer->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($loadBalancer->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($loadBalancer->status)->toBe(InfrastructureStatus::Healthy);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var LoadBalancer $loadBalancer */
        $loadBalancer = LoadBalancer::factory()->create();

        expect($loadBalancer->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var LoadBalancer $loadBalancer */
        $loadBalancer = LoadBalancer::factory()
            ->create()
            ->refresh();

        expect(array_keys($loadBalancer->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'infrastructure_id',
                'name',
                'external_load_balancer_id',
                'ip',
                'status',
            ]);
    });
