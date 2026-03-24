<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Data\SessionUserData;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

abstract class AuthenticatedCommand extends Command
{
    protected bool $requiresOrganization = false;

    protected SessionUserData $user;

    protected ?SessionOrganizationData $organization = null;

    final public function handle(SessionManager $session): int
    {
        if (! $session->isAuthenticated()) {
            $this->components->error('You are not logged in. Run [user:login] first.');

            return self::FAILURE;
        }

        $userData = $session->getUser();

        if ($userData === null) {
            $this->components->error('Session is corrupted. Run [user:login] again.');
            $session->clear();

            return self::FAILURE;
        }

        $accessToken = PersonalAccessToken::findToken($userData->token);

        if ($accessToken === null) {
            $this->components->error('Your session has expired. Run [user:login] again.');
            $session->clear();

            return self::FAILURE;
        }

        $this->user = $userData;

        if ($this->requiresOrganization) {
            $organization = $session->getOrganization();

            if ($organization === null) {
                $this->components->error('No organization selected. Run [organization:select] first.');

                return self::FAILURE;
            }

            $this->organization = $organization;
        }

        return $this->laravel->call([$this, 'handleCommand']);
    }
}
