<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlatformRole;
use App\Models\User;

final class PromoteUserToAdmin
{
    public function handle(User $user): User
    {
        $user->update(['platform_role' => PlatformRole::Admin]);

        return $user->refresh();
    }
}
