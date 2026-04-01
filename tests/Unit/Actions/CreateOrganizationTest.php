<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Data\CreateOrganizationData;
use App\Enums\OrganizationRole;
use App\Models\User;

test('creates organization with auto-generated slug',
    /**
     * @throws Throwable
     */
    function (): void {
        $action = app(CreateOrganization::class);

        $organization = $action->handle(new CreateOrganizationData(
            name: 'Test Organization',
            description: 'This is a test organization',
        ));

        expect($organization->name)->toBe('Test Organization')
            ->and($organization->slug)->toBe('test-organization')
            ->and($organization->description)->toBe('This is a test organization');
    });

test('attaches owner with Owner role',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $action = app(CreateOrganization::class);

        $organization = $action->handle(
            new CreateOrganizationData(name: 'Acme Corp'),
            owner: $user,
        );

        expect($organization->users)->toHaveCount(1)
            ->and($organization->users->first()->id)->toBe($user->id)
            ->and($organization->users->first()->pivot->role)->toBe(OrganizationRole::Owner);
    });

test('sets current organization on owner after creation',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $action = app(CreateOrganization::class);

        $organization = $action->handle(
            new CreateOrganizationData(name: 'Acme Corp'),
            owner: $user,
        );

        $user->refresh();

        expect($user->current_organization_id)->toBe($organization->id);
    });
