<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\PromoteUserToAdmin;
use App\Enums\PlatformRole;
use App\Queries\UserQuery;
use Illuminate\Console\Command;

final class PromoteUserAdminCommand extends Command
{
    /** @var string */
    protected $signature = 'user:promote-admin {email : The email of the user to promote}';

    /** @var string */
    protected $description = 'Promote a user to platform admin';

    public function handle(UserQuery $userQuery, PromoteUserToAdmin $promoteUserToAdmin): int
    {
        /** @var string $email */
        $email = $this->argument('email');

        $user = ($userQuery)()->byEmail($email)->first();

        if (! $user) {
            $this->components->error("User with email [{$email}] not found.");

            return self::FAILURE;
        }

        if ($user->platform_role === PlatformRole::Admin) {
            $this->components->info("User [{$email}] is already an admin.");

            return self::SUCCESS;
        }

        $promoteUserToAdmin->handle($user);

        $this->components->info("User [{$email}] promoted to admin.");

        return self::SUCCESS;
    }
}
