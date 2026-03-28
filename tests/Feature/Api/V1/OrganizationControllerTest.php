<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

test('store creates an organization and returns organization data',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.organizations.store'), [
                'name' => 'Acme Corp',
                'description' => 'A great company',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description'],
            ])
            ->assertJsonPath('data.name', 'Acme Corp')
            ->assertJsonPath('data.slug', 'acme-corp');

        $this->assertDatabaseHas('organizations', ['name' => 'Acme Corp']);
        $this->assertDatabaseHas('organization_user', [
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    });

test('store validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.organizations.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

test('store requires authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->postJson(route('api.v1.organizations.store'), [
            'name' => 'Acme Corp',
        ]);

        $response->assertUnauthorized();
    });

test('index returns organizations for authenticated user',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $org1 = Organization::factory()->create(['name' => 'Acme Corp']);
        $org2 = Organization::factory()->create(['name' => 'Beta Inc']);
        Organization::factory()->create(['name' => 'Other Corp']);

        $user->organizations()->attach($org1, ['role' => 'owner']);
        $user->organizations()->attach($org2, ['role' => 'member']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.organizations.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Acme Corp')
            ->assertJsonPath('data.1.name', 'Beta Inc');
    });

test('index returns empty array when user has no organizations',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.organizations.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

test('index requires authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->getJson(route('api.v1.organizations.index'));

        $response->assertUnauthorized();
    });
