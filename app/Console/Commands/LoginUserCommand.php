<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class LoginUserCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'user:login';

    /**
     * @var string
     */
    protected $description = 'Log in to your account';

    public function handle(LoginUser $loginUser, SessionManager $session): int
    {
        $email = text(
            label: 'Email',
            required: true,
            validate: ['email' => 'required|email'],
        );

        $inputPassword = password(
            label: 'Password',
            required: true,
        );

        $userData = $loginUser->handle($email, $inputPassword);

        if ($userData === null) {
            $this->components->error('Invalid credentials.');

            return self::FAILURE;
        }

        $session->setUser($userData);

        $this->components->info("Logged in as [{$userData->name}].");

        return self::SUCCESS;
    }
}
