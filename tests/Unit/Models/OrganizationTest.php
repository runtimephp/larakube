<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Server;

test('to array', function (): void {

    /** @var Organization $organization */
    $organization = Organization::factory()
        ->create()
        ->fresh();

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

test('has servers relationship', function (): void {
    $organization = Organization::factory()->create();
    Server::factory()->create(['organization_id' => $organization->id]);

    expect($organization->servers)->toHaveCount(1);
});

test('has cloud providers relationship', function (): void {
    $organization = Organization::factory()->create();

    expect($organization->cloudProviders)->toBeEmpty();
});

test('has infrastructures relationship', function (): void {
    $organization = Organization::factory()->create();

    expect($organization->infrastructures)->toBeEmpty();
});
