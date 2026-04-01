<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

test('currentOrganization returns the current organization',
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

        expect($user->currentOrganization)->toBeInstanceOf(Organization::class)
            ->and($user->currentOrganization->id)->toBe($organization->id);
    });

test('currentOrganization returns null when not set',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'current_organization_id' => null,
        ]);

        expect($user->currentOrganization)->toBeNull();
    });

test('belongsToOrganization returns true for member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'member']);

        expect($user->belongsToOrganization($organization))->toBeTrue();
    });

test('belongsToOrganization returns false for non-member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();

        expect($user->belongsToOrganization($organization))->toBeFalse();
    });
