<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Region;
use App\Models\Server;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

test('creates cloud provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create([
            'name' => 'My Hetzner',
        ]);

        expect($cloudProvider->name)->toBe('My Hetzner')
            ->and($cloudProvider->id)->toBeString()
            ->and($cloudProvider->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to organization',
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

        expect($cloudProvider->organization)->toBeInstanceOf(Organization::class)
            ->and($cloudProvider->organization->id)->toBe($organization->id);
    });

test('has many servers',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create();

        /** @var Server $server */
        $server = Server::factory()->create([
            'cloud_provider_id' => $cloudProvider->id,
        ]);

        expect($cloudProvider->servers)->toHaveCount(1)
            ->and($cloudProvider->servers->first())->toBeInstanceOf(Server::class)
            ->and($cloudProvider->servers->first()->id)->toBe($server->id);
    });

test('has many regions',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create();

        /** @var Region $region */
        $region = Region::factory()->create([
            'cloud_provider_id' => $cloudProvider->id,
        ]);

        expect($cloudProvider->regions)->toHaveCount(1)
            ->and($cloudProvider->regions->first())->toBeInstanceOf(Region::class)
            ->and($cloudProvider->regions->first()->id)->toBe($region->id);
    });

test('has many infrastructures',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'cloud_provider_id' => $cloudProvider->id,
        ]);

        expect($cloudProvider->infrastructures)->toHaveCount(1)
            ->and($cloudProvider->infrastructures->first())->toBeInstanceOf(Infrastructure::class)
            ->and($cloudProvider->infrastructures->first()->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->hetzner()->create([
            'api_token' => 'my-secret-token',
        ]);

        $raw = DB::table('cloud_providers')
            ->where('id', $cloudProvider->id)
            ->value('api_token');

        expect($cloudProvider->id)->toBeString()
            ->and($cloudProvider->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($cloudProvider->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($cloudProvider->type)->toBe(CloudProviderType::Hetzner)
            ->and($raw)->not->toBe('my-secret-token')
            ->and($cloudProvider->fresh()->api_token)->toBe('my-secret-token');
    });

test('api token is encrypted',
    /**
     * @throws Throwable
     */
    function (): void {
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

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create();

        expect($cloudProvider->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()
            ->create()
            ->refresh();

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
