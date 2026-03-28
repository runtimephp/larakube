<?php

declare(strict_types=1);

namespace App\Actions;

use App\Console\Services\SessionManager;
use App\Queries\UserQuery;

final readonly class LogoutUser
{
    public function __construct(private UserQuery $userQuery) {}

    public function handle(SessionManager $session): void
    {
        $userData = $session->getUser();

        if ($userData !== null) {
            $user = ($this->userQuery)()->byEmail($userData->email)->first();
            $user?->tokens()->delete();
        }

        $session->clear();
    }
}
