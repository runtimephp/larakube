<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('general settings page can be rendered for organization members', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    $user->organizations()->attach($organization, ['role' => OrganizationRole::Member]);

    $response = $this->actingAs($user)->get("/{$organization->slug}/settings/general");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('organization-general-settings/edit')
        ->where('organization.id', $organization->id)
        ->where('organization.slug', $organization->slug)
        ->where('can.update', false)
    );
});

test('organization settings placeholder pages can be rendered for organization members', function (
    string $path,
    string $component,
    string $title,
    ?array $stats = null,
): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    $user->organizations()->attach($organization, ['role' => OrganizationRole::Member]);

    $response = $this->actingAs($user)->get("/{$organization->slug}/settings/{$path}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component($component)
        ->where('organization.id', $organization->id)
        ->where('section.title', $title)
        ->when($stats !== null, fn ($page) => $page->where('stats.connected', $stats['connected']))
    );
})->with([
    ['members', 'organization-settings-placeholder/index', 'Members', null],
    ['billing', 'organization-settings-placeholder/index', 'Billing', null],
    // cloud-providers now has its own controller and page — tested in OrganizationCloudProvidersTest
    ['danger-zone', 'organization-settings-placeholder/index', 'Danger Zone', null],
]);

test('organization owner can update general settings', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    $user->organizations()->attach($organization, ['role' => OrganizationRole::Owner]);

    $response = $this->actingAs($user)->patch("/{$organization->slug}/settings/general", [
        'name' => 'Acme Platform',
        'description' => 'Updated organization description.',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$organization->slug}/settings/general");

    $organization->refresh();

    expect($organization->name)->toBe('Acme Platform')
        ->and($organization->description)->toBe('Updated organization description.');
});

test('organization member cannot update general settings', function (): void {
    $organization = Organization::factory()->create();
    $user = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    $user->organizations()->attach($organization, ['role' => OrganizationRole::Member]);

    $response = $this->actingAs($user)->patch("/{$organization->slug}/settings/general", [
        'name' => 'Blocked Update',
        'description' => 'This should fail.',
    ]);

    $response->assertForbidden();

    expect($organization->refresh()->name)->not->toBe('Blocked Update');
});

test('organization owner can update logo', function (): void {
    Storage::fake('public');

    $organization = Organization::factory()->create();
    $user = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    $user->organizations()->attach($organization, ['role' => OrganizationRole::Owner]);

    $response = $this->actingAs($user)->patch("/{$organization->slug}/settings/logo", [
        'logo' => UploadedFile::fake()->image('avatar.png'),
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$organization->slug}/settings/general");

    $organization->refresh();

    expect($organization->logo)->toStartWith('/storage/organizations/logos/');

    Storage::disk('public')->assertExists(str($organization->logo)->after('/storage/')->toString());
});
