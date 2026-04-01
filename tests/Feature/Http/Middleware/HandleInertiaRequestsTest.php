<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

test('shares current organization and organizations list with authenticated user',
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
        $user->organizations()->attach($organization, ['role' => 'owner']);

        $response = $this->actingAs($user)->get("/{$organization->slug}/dashboard");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('currentOrganization')
            ->where('currentOrganization.id', $organization->id)
            ->where('currentOrganization.name', $organization->name)
            ->where('currentOrganization.slug', $organization->slug)
            ->has('organizations', 1)
            ->where('organizations.0.id', $organization->id)
        );
    });

test('shares null organization data for unauthenticated requests',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('currentOrganization', null)
            ->where('organizations', null)
        );
    });
