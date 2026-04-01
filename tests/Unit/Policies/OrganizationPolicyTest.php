<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;

test('view returns true for organization member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'member']);

        expect($user->can('view', $organization))->toBeTrue();
    });

test('view returns false for non-member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();

        expect($user->can('view', $organization))->toBeFalse();
    });

test('switch returns true for organization member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'member']);

        expect($user->can('switch', $organization))->toBeTrue();
    });

test('switch returns false for non-member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();

        expect($user->can('switch', $organization))->toBeFalse();
    });

test('updateSettings returns true for organization owner',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'owner']);

        expect($user->can('updateSettings', $organization))->toBeTrue();
    });

test('updateSettings returns true for organization admin',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'admin']);

        expect($user->can('updateSettings', $organization))->toBeTrue();
    });

test('updateSettings returns false for organization member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'member']);

        expect($user->can('updateSettings', $organization))->toBeFalse();
    });
