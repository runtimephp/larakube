<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\AuthClient;
use App\Data\CreateUserData;
use App\Exceptions\LarakubeApiException;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class RegisterUserCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'user:register';

    /**
     * @var string
     */
    protected $description = 'Register a new user';

    public function handle(AuthClient $authClient): int
    {
        $name = text(
            label: 'Name',
            required: true,
        );

        $email = text(
            label: 'Email',
            required: true,
        );

        $password = password(
            label: 'Password',
            required: true,
        );

        try {
            $user = $authClient->register(new CreateUserData(
                name: $name,
                email: $email,
                password: $password,
            ));
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("User [{$user->name}] registered successfully with ID [{$user->id}].");

        return self::SUCCESS;
    }
}
