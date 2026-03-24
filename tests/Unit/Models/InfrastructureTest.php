<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Region;

test('to array', function (): void {

    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()
        ->create()
        ->fresh();

    expect(array_keys($infrastructure->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'organization_id',
            'cloud_provider_id',
            'region_id',
            'name',
            'description',
            'status',
        ]);
});

test('status is cast to enum', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create([
        'status' => InfrastructureStatus::Healthy,
    ]);

    expect($infrastructure->status)->toBe(InfrastructureStatus::Healthy);
});

test('belongs to organization', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->organization)
        ->toBeInstanceOf(Organization::class);
});

test('belongs to cloud provider', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->cloudProvider)
        ->toBeInstanceOf(CloudProvider::class);
});

test('belongs to region', function (): void {
    /** @var Region $region */
    $region = Region::factory()->create();

    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create([
        'region_id' => $region->id,
    ]);

    expect($infrastructure->region)
        ->toBeInstanceOf(Region::class);
});

test('has many kubernetes clusters', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->kubernetesClusters)->toBeEmpty();
});

test('has many networks', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->networks)->toBeEmpty();
});

test('has many firewalls', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->firewalls)->toBeEmpty();
});

test('has many load balancers', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->loadBalancers)->toBeEmpty();
});

test('has many storages', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->storages)->toBeEmpty();
});

test('has many backups', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->backups)->toBeEmpty();
});

test('has many ssh keys', function (): void {
    /** @var Infrastructure $infrastructure */
    $infrastructure = Infrastructure::factory()->create();

    expect($infrastructure->sshKeys)->toBeEmpty();
});
