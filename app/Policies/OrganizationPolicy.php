<?php

declare(strict_types=1);

namespace App\Policies;

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
}
