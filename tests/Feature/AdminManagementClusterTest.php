<?php

declare(strict_types=1);

use App\Enums\ManagementClusterStatus;
use App\Enums\PlatformRole;
use App\Models\ManagementCluster;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a platform administrator can view the management clusters list', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var ManagementCluster $cluster */
    $cluster = ManagementCluster::factory()->ready()->create([
        'name' => 'mgmt-production',
        'provider' => 'hetzner',
        'region' => 'eu-central',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.management-clusters.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/management-clusters/index')
            ->has('clusters', 1)
            ->where('clusters.0.id', $cluster->id)
            ->where('clusters.0.name', 'mgmt-production')
            ->where('clusters.0.provider', 'hetzner')
            ->where('clusters.0.region', 'eu-central')
            ->where('clusters.0.status', ManagementClusterStatus::Ready->value)
            ->has('clusters.0.created_at')
        );
});

test('a platform administrator sees an empty list when no management clusters exist', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.management-clusters.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/management-clusters/index')
            ->has('clusters', 0)
        );
});

test('a non-platform administrator is forbidden from accessing management clusters', function () {
    /** @var User $user */
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    $this->actingAs($user)
        ->get(route('admin.management-clusters.index'))
        ->assertForbidden();
});

test('a guest is redirected to login when accessing management clusters', function () {
    $this->get(route('admin.management-clusters.index'))
        ->assertRedirect(route('login'));
});

test('a platform administrator can view a single management cluster', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var ManagementCluster $cluster */
    $cluster = ManagementCluster::factory()->ready()->create([
        'name' => 'mgmt-production',
        'provider' => 'hetzner',
        'region' => 'eu-central',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.management-clusters.show', $cluster))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/management-clusters/show')
            ->where('cluster.id', $cluster->id)
            ->where('cluster.name', 'mgmt-production')
            ->where('cluster.provider', 'hetzner')
            ->where('cluster.region', 'eu-central')
            ->where('cluster.status', ManagementClusterStatus::Ready->value)
            ->where('cluster.kubernetes_version', 'v1.32.3')
            ->has('cluster.created_at')
        );
});

test('a non-platform administrator is forbidden from viewing a management cluster', function () {
    /** @var User $user */
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    /** @var ManagementCluster $cluster */
    $cluster = ManagementCluster::factory()->ready()->create();

    $this->actingAs($user)
        ->get(route('admin.management-clusters.show', $cluster))
        ->assertForbidden();
});

test('a guest is redirected to login when viewing a management cluster', function () {
    /** @var ManagementCluster $cluster */
    $cluster = ManagementCluster::factory()->ready()->create();

    $this->get(route('admin.management-clusters.show', $cluster))
        ->assertRedirect(route('login'));
});
