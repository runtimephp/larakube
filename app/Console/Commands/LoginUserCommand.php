<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Contracts\AuthClient;
use App\Exceptions\LarakubeApiException;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class LoginUserCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'user:login {--email= : User email} {--password= : User password}';

    /**
     * @var string
     */
    protected $description = 'Log in to your account';

    public function handle(AuthClient $authClient, SessionManager $session): int
    {
        $email = $this->option('email');
        $password = $this->option('password');

        if (! $email) {
            $email = text(
                label: 'Email',
                required: true,
            );
        }

        if (! $password) {
            $password = password(
                label: 'Password',
                required: true,
            );
        }

        try {
            $userData = $authClient->login($email, $password);
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $session->setUser($userData);
        $session->clearOrganization();
        $session->clearInfrastructure();

        $this->components->info("Logged in as [{$userData->name}].");

        return self::SUCCESS;
    }
}
