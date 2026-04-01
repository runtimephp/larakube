<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

final class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    public function switch(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    public function updateSettings(User $user, Organization $organization): bool
    {
        $role = $user->organizations()
            ->where('organizations.id', $organization->id)
            ->value('role');

        return in_array($role, [OrganizationRole::Owner->value, OrganizationRole::Admin->value], true);
    }
}
