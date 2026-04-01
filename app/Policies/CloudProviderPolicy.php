<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

final class CloudProviderPolicy
{
    /**
     * Determine whether the user can view cloud providers for an organization.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    /**
     * Determine whether the user can manage (create/delete) cloud providers for an organization.
     */
    public function manage(User $user, Organization $organization): bool
    {
        $role = $user->organizations()
            ->where('organizations.id', $organization->id)
            ->value('role');

        return in_array($role, [OrganizationRole::Owner->value, OrganizationRole::Admin->value], true);
    }

    /**
     * Determine whether the user can delete a specific cloud provider.
     */
    public function delete(User $user, CloudProvider $cloudProvider): bool
    {
        return $this->manage($user, $cloudProvider->organization);
    }
}
