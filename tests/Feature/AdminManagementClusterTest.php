<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('a platform administrator can successfully navigate to /admin/management-clusters', function () {
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.management-clusters.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/management-clusters/index'));
});

it('a non-platform administrator is forbidden from accessing /admin/management-clusters', function () {
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    $this->actingAs($user)
        ->get(route('admin.management-clusters.index'))
        ->assertForbidden();
});

it('a guest user is redirected to login when trying to access /admin/management-clusters', function () {
    $this->get(route('admin.management-clusters.index'))
        ->assertRedirect(route('login'));
});
