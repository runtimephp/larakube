<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlatformRole;
use App\Models\ManagementCluster;
use App\Models\User;

final class ManagementClusterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->platform_role === PlatformRole::Admin;
    }

    public function view(User $user, ManagementCluster $managementCluster): bool
    {
        return $user->platform_role === PlatformRole::Admin;
    }

    public function create(User $user): bool
    {
        return $user->platform_role === PlatformRole::Admin;
    }

    public function update(User $user, ManagementCluster $managementCluster): bool
    {
        return $user->platform_role === PlatformRole::Admin;
    }

    public function delete(User $user, ManagementCluster $managementCluster): bool
    {
        return $user->platform_role === PlatformRole::Admin;
    }
}
