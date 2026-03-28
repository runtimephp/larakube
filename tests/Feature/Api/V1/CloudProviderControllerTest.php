<?php

declare(strict_types=1);

use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    /** @var User $user */
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organizations()->attach($this->organization, ['role' => 'owner']);

    $hetznerService = useInMemoryHetznerService(true);
    bindInMemoryHetznerFactory($hetznerService);
});

test('store creates a cloud provider and returns data',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->postJson(route('api.v1.cloud-providers.store'), [
                'name' => 'Hetzner Production',
                'type' => 'hetzner',
                'api_token' => 'valid-token',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'type', 'is_verified'],
            ])
            ->assertJsonPath('data.name', 'Hetzner Production')
            ->assertJsonPath('data.type', 'hetzner')
            ->assertJsonPath('data.is_verified', true);

        $this->assertDatabaseHas('cloud_providers', [
            'organization_id' => $this->organization->id,
            'name' => 'Hetzner Production',
        ]);
    });

test('store fails with invalid token',
    /**
     * @throws Throwable
     */
    function (): void {
        $hetznerService = useInMemoryHetznerService(false);
        bindInMemoryHetznerFactory($hetznerService);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->postJson(route('api.v1.cloud-providers.store'), [
                'name' => 'Hetzner Staging',
                'type' => 'hetzner',
                'api_token' => 'invalid-token',
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'The API token for Hetzner is invalid.');
    });

test('store validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->postJson(route('api.v1.cloud-providers.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'type', 'api_token']);
    });

test('index returns cloud providers for organization',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->hetzner()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Hetzner Production',
        ]);

        $otherOrg = Organization::factory()->create();
        CloudProvider::factory()->hetzner()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Other Provider',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->getJson(route('api.v1.cloud-providers.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Hetzner Production');
    });

test('destroy deletes a cloud provider',
    /**
     * @throws Throwable
     */
    function (): void {
        $provider = CloudProvider::factory()->hetzner()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $this->organization->id])
            ->deleteJson(route('api.v1.cloud-providers.destroy', $provider));

        $response->assertNoContent();

        $this->assertDatabaseMissing('cloud_providers', ['id' => $provider->id]);
    });

test('all endpoints require authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->postJson(route('api.v1.cloud-providers.store'))->assertUnauthorized();
        $this->getJson(route('api.v1.cloud-providers.index'))->assertUnauthorized();
        $this->deleteJson(route('api.v1.cloud-providers.destroy', 'fake-id'))->assertUnauthorized();
    });

test('all endpoints require organization header',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.v1.cloud-providers.store'))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed');

        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.cloud-providers.index'))
            ->assertUnprocessable();
    });
