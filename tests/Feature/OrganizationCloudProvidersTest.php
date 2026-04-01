<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();

    /** @var User $owner */
    $this->owner = User::factory()->create(['current_organization_id' => $this->organization->id]);
    $this->owner->organizations()->attach($this->organization, ['role' => OrganizationRole::Owner]);

    /** @var User $member */
    $this->member = User::factory()->create(['current_organization_id' => $this->organization->id]);
    $this->member->organizations()->attach($this->organization, ['role' => OrganizationRole::Member]);
});

test('cloud providers page renders for organization members', function (): void {
    CloudProvider::factory()->hetzner()->create(['organization_id' => $this->organization->id]);

    $response = $this->actingAs($this->member)->get("/{$this->organization->slug}/settings/cloud-providers");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('organization-cloud-providers/index')
        ->where('organization.id', $this->organization->id)
        ->where('can.manage', false)
        ->has('cloudProviders', 1)
    );
});

test('cloud providers page shows can.manage true for owner', function (): void {
    $response = $this->actingAs($this->owner)->get("/{$this->organization->slug}/settings/cloud-providers");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('can.manage', true));
});

test('cloud providers page only lists providers for the current organization', function (): void {
    CloudProvider::factory()->hetzner()->create(['organization_id' => $this->organization->id]);
    $otherOrg = Organization::factory()->create();
    CloudProvider::factory()->hetzner()->create(['organization_id' => $otherOrg->id]);

    $response = $this->actingAs($this->owner)->get("/{$this->organization->slug}/settings/cloud-providers");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('cloudProviders', 1));
});

test('owner can create a cloud provider', function (): void {
    $hetznerService = useInMemoryHetznerService(true);
    bindInMemoryHetznerFactory($hetznerService);

    $response = $this->actingAs($this->owner)->post("/{$this->organization->slug}/settings/cloud-providers", [
        'name' => 'My Hetzner',
        'type' => 'hetzner',
        'api_token' => 'valid-token',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$this->organization->slug}/settings/cloud-providers");

    $this->assertDatabaseHas('cloud_providers', [
        'organization_id' => $this->organization->id,
        'name' => 'My Hetzner',
        'is_verified' => true,
    ]);
});

test('member cannot create a cloud provider', function (): void {
    $response = $this->actingAs($this->member)->post("/{$this->organization->slug}/settings/cloud-providers", [
        'name' => 'Blocked',
        'type' => 'hetzner',
        'api_token' => 'valid-token',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseEmpty('cloud_providers');
});

test('store returns validation error when api token is invalid', function (): void {
    $hetznerService = useInMemoryHetznerService(false);
    bindInMemoryHetznerFactory($hetznerService);

    $response = $this->actingAs($this->owner)->post("/{$this->organization->slug}/settings/cloud-providers", [
        'name' => 'My Hetzner',
        'type' => 'hetzner',
        'api_token' => 'invalid-token',
    ]);

    $response->assertSessionHasErrors(['api_token']);
    $this->assertDatabaseEmpty('cloud_providers');
});

test('store validates required fields', function (): void {
    $response = $this->actingAs($this->owner)->post("/{$this->organization->slug}/settings/cloud-providers", []);

    $response->assertSessionHasErrors(['name', 'type', 'api_token']);
});

test('owner can delete a cloud provider', function (): void {
    $provider = CloudProvider::factory()->hetzner()->create(['organization_id' => $this->organization->id]);

    $response = $this->actingAs($this->owner)
        ->delete("/{$this->organization->slug}/settings/cloud-providers/{$provider->id}");

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$this->organization->slug}/settings/cloud-providers");

    $this->assertModelMissing($provider);
});

test('member cannot delete a cloud provider', function (): void {
    $provider = CloudProvider::factory()->hetzner()->create(['organization_id' => $this->organization->id]);

    $response = $this->actingAs($this->member)
        ->delete("/{$this->organization->slug}/settings/cloud-providers/{$provider->id}");

    $response->assertForbidden();
    $this->assertModelExists($provider);
});
