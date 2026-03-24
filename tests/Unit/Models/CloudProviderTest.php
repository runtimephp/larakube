<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\Organization;

test('to array', function (): void {

    /** @var CloudProvider $cloudProvider */
    $cloudProvider = CloudProvider::factory()
        ->create()
        ->fresh();

    expect(array_keys($cloudProvider->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'organization_id',
            'name',
            'type',
            'api_token',
            'is_verified',
        ]);
});

test('type is cast to enum', function (): void {
    /** @var CloudProvider $cloudProvider */
    $cloudProvider = CloudProvider::factory()->hetzner()->create();

    expect($cloudProvider->type)->toBe(CloudProviderType::Hetzner);
});

test('api token is encrypted', function (): void {
    /** @var CloudProvider $cloudProvider */
    $cloudProvider = CloudProvider::factory()->create([
        'api_token' => 'my-secret-token',
    ]);

    $raw = DB::table('cloud_providers')
        ->where('id', $cloudProvider->id)
        ->value('api_token');

    expect($raw)->not->toBe('my-secret-token')
        ->and($cloudProvider->fresh()->api_token)->toBe('my-secret-token');
});

test('belongs to organization', function (): void {
    /** @var CloudProvider $cloudProvider */
    $cloudProvider = CloudProvider::factory()->create();

    expect($cloudProvider->organization)
        ->toBeInstanceOf(Organization::class);
});

test('has many servers', function (): void {
    /** @var CloudProvider $cloudProvider */
    $cloudProvider = CloudProvider::factory()->create();

    expect($cloudProvider->servers)->toBeEmpty();
});

test('has many regions', function (): void {
    /** @var CloudProvider $cloudProvider */
    $cloudProvider = CloudProvider::factory()->create();

    expect($cloudProvider->regions)->toBeEmpty();
});

test('has many infrastructures', function (): void {
    /** @var CloudProvider $cloudProvider */
    $cloudProvider = CloudProvider::factory()->create();

    expect($cloudProvider->infrastructures)->toBeEmpty();
});
