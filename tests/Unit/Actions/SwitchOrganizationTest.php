<?php

declare(strict_types=1);

use App\Actions\SwitchOrganization;
use App\Models\Organization;
use App\Models\User;

test('switches the user current organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'owner']);

        app(SwitchOrganization::class)->handle($user, $organization);

        $user->refresh();

        expect($user->current_organization_id)->toBe($organization->id)
            ->and($user->currentOrganization->id)->toBe($organization->id);
    });

test('throws exception when user is not a member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();

        app(SwitchOrganization::class)->handle($user, $organization);
    })->throws(Illuminate\Auth\Access\AuthorizationException::class);
