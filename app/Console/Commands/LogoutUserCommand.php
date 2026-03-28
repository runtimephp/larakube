<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Contracts\AuthClient;
use App\Exceptions\LarakubeApiException;
use Illuminate\Console\Command;

final class LogoutUserCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'user:logout';

    /**
     * @var string
     */
    protected $description = 'Log out and clear session';

    public function handle(AuthClient $authClient, SessionManager $session): int
    {
        try {
            $authClient->logout();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $session->clear();

        $this->components->info('Logged out successfully.');

        return self::SUCCESS;
    }
}
