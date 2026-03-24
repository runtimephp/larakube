<?php

declare(strict_types=1);

use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\Server;

test('to array', function (): void {

    /** @var Server $server */
    $server = Server::factory()
        ->create()
        ->fresh();

    expect(array_keys($server->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'organization_id',
            'cloud_provider_id',
            'external_id',
            'name',
            'status',
            'type',
            'region',
            'ipv4',
            'ipv6',
            'metadata',
        ]);
});

test('status is cast to enum', function (): void {
    $server = Server::factory()->running()->create();

    expect($server->status)->toBe(ServerStatus::Running);
});

test('belongs to organization', function (): void {
    $server = Server::factory()->create();

    expect($server->organization)->toBeInstanceOf(Organization::class);
});

test('belongs to cloud provider', function (): void {
    $server = Server::factory()->create();

    expect($server->cloudProvider)->toBeInstanceOf(CloudProvider::class);
});
