<?php

declare(strict_types=1);

namespace App\Actions;

use App\Console\Services\SessionManager;
use App\Models\User;

final class LogoutUser
{
    public function handle(SessionManager $session): void
    {
        $userData = $session->getUser();

        if ($userData !== null) {
            $user = User::query()->find($userData->id);
            $user?->tokens()->delete();
        }

        $session->clear();
    }
}
