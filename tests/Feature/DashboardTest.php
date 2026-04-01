<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users with organization are redirected to org dashboard', function () {
    /** @var Organization $organization */
    $organization = Organization::factory()->create();

    /** @var User $user */
    $user = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);
    $user->organizations()->attach($organization, ['role' => 'member']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect("/{$organization->slug}/dashboard");
});

test('authenticated users without organization are redirected to create', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('organizations.create'));
});

test('authenticated users can visit the org-scoped dashboard', function () {
    /** @var Organization $organization */
    $organization = Organization::factory()->create();

    /** @var User $user */
    $user = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);
    $user->organizations()->attach($organization, ['role' => 'member']);

    $this->actingAs($user)
        ->get("/{$organization->slug}/dashboard")
        ->assertOk();
});
