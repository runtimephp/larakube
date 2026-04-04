<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Models\ManagementCluster;
use App\Models\User;

test('admin can create management clusters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Admin]);

        expect($user->can('create', ManagementCluster::class))->toBeTrue();
    });

test('member cannot create management clusters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

        expect($user->can('create', ManagementCluster::class))->toBeFalse();
    });

test('admin can view a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Admin]);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect($user->can('view', $cluster))->toBeTrue();
    });

test('member cannot view a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect($user->can('view', $cluster))->toBeFalse();
    });

test('admin can delete a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Admin]);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect($user->can('delete', $cluster))->toBeTrue();
    });

test('member cannot delete a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect($user->can('delete', $cluster))->toBeFalse();
    });

test('admin can update a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Admin]);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect($user->can('update', $cluster))->toBeTrue();
    });

test('member cannot update a management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect($user->can('update', $cluster))->toBeFalse();
    });
