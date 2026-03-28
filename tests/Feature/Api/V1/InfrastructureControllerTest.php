<?php

declare(strict_types=1);

use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    /** @var User $user */
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organizations()->attach($this->organization, ['role' => 'owner']);
    $this->provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $this->organization->id,
    ]);
});

test('store creates an infrastructure and returns data',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->postJson(route('api.v1.infrastructures.store'), [
                'name' => 'Production',
                'description' => 'Production infrastructure',
                'cloud_provider_id' => $this->provider->id,
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'description', 'status', 'cloud_provider_id'],
            ])
            ->assertJsonPath('data.name', 'Production')
            ->assertJsonPath('data.cloud_provider_id', $this->provider->id);

        $this->assertDatabaseHas('infrastructures', [
            'name' => 'Production',
            'cloud_provider_id' => $this->provider->id,
            'organization_id' => $this->organization->id,
        ]);
    });

test('store validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->postJson(route('api.v1.infrastructures.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'cloud_provider_id']);
    });

test('index returns infrastructures for organization',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'name' => 'Production',
        ]);

        $otherOrg = Organization::factory()->create();
        Infrastructure::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Other Infra',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->getJson(route('api.v1.infrastructures.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Production');
    });

test('all endpoints require authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->postJson(route('api.v1.infrastructures.store'))->assertUnauthorized();
        $this->getJson(route('api.v1.infrastructures.index'))->assertUnauthorized();
    });

test('all endpoints require organization header',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.v1.infrastructures.store'))
            ->assertUnprocessable();

        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.infrastructures.index'))
            ->assertUnprocessable();
    });
