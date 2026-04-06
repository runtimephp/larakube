<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlatformRole;
use App\Models\Provider;
use App\Models\User;

final class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->platform_role === PlatformRole::Admin;
    }

    public function view(User $user, Provider $provider): bool
    {
        return $user->platform_role === PlatformRole::Admin;
    }
}
