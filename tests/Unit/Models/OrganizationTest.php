<?php

declare(strict_types=1);

use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Server;
use Carbon\CarbonImmutable;

test('creates organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create([
            'name' => 'Acme Corp',
        ]);

        expect($organization->name)->toBe('Acme Corp')
            ->and($organization->id)->toBeString()
            ->and($organization->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('has many servers',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'organization_id' => $organization->id,
        ]);

        expect($organization->servers)->toHaveCount(1)
            ->and($organization->servers->first())->toBeInstanceOf(Server::class)
            ->and($organization->servers->first()->id)->toBe($server->id);
    });

test('has many cloud providers',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create([
            'organization_id' => $organization->id,
        ]);

        expect($organization->cloudProviders)->toHaveCount(1)
            ->and($organization->cloudProviders->first())->toBeInstanceOf(CloudProvider::class)
            ->and($organization->cloudProviders->first()->id)->toBe($cloudProvider->id);
    });

test('has many infrastructures',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'organization_id' => $organization->id,
        ]);

        expect($organization->infrastructures)->toHaveCount(1)
            ->and($organization->infrastructures->first())->toBeInstanceOf(Infrastructure::class)
            ->and($organization->infrastructures->first()->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        expect($organization->id)->toBeString()
            ->and($organization->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($organization->updated_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        expect($organization->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()
            ->create()
            ->refresh();

        expect(array_keys($organization->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'name',
                'slug',
                'logo',
                'description',
            ]);
    });
