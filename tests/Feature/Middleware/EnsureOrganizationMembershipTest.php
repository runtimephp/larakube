<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

test('redirects user with no organizations to organizations.create',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $response = $this->actingAs($user)->get("/{$organization->slug}/dashboard");

        $response->assertRedirect(route('organizations.create'));
    });

test('allows access when user is a member of the organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create([
            'current_organization_id' => $organization->id,
        ]);
        $user->organizations()->attach($organization, ['role' => 'member']);

        $response = $this->actingAs($user)->get("/{$organization->slug}/dashboard");

        $response->assertOk();
    });

test('returns 403 when user is not a member of the organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var Organization $otherOrg */
        $otherOrg = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($otherOrg, ['role' => 'member']);

        $response = $this->actingAs($user)->get("/{$organization->slug}/dashboard");

        $response->assertForbidden();
    });

test('auto-switches current organization when URL org differs from stored',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $orgA */
        $orgA = Organization::factory()->create();

        /** @var Organization $orgB */
        $orgB = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create([
            'current_organization_id' => $orgA->id,
        ]);
        $user->organizations()->attach($orgA, ['role' => 'member']);
        $user->organizations()->attach($orgB, ['role' => 'member']);

        $this->actingAs($user)->get("/{$orgB->slug}/dashboard");

        $user->refresh();
        expect($user->current_organization_id)->toBe($orgB->id);
    });

test('returns 404 for non-existent organization slug',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/non-existent-org/dashboard');

        $response->assertNotFound();
    });
