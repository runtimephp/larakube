<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\LogoutUser;
use App\Console\Services\SessionManager;
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

    public function handle(LogoutUser $logoutUser, SessionManager $session): int
    {
        $logoutUser->handle($session);

        $this->components->info('Logged out successfully.');

        return self::SUCCESS;
    }
}
