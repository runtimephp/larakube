<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

test('switches organization and redirects to dashboard',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'member']);

        $response = $this->actingAs($user)->post("/organizations/{$organization->slug}/switch");

        $response->assertRedirect("/{$organization->slug}/dashboard");

        $user->refresh();
        expect($user->current_organization_id)->toBe($organization->id);
    });

test('returns 403 when user is not a member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post("/organizations/{$organization->slug}/switch");

        $response->assertForbidden();
    });

test('redirects unauthenticated users to login',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $response = $this->post("/organizations/{$organization->slug}/switch");

        $response->assertRedirect('/login');
    });
