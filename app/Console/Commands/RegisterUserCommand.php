<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateUser;
use App\Data\CreateUserData;
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

    public function handle(CreateUser $createUser): int
    {
        $name = text(
            label: 'Name',
            required: true,
        );

        $email = text(
            label: 'Email',
            required: true,
            validate: ['email' => 'required|email|unique:users,email'],
        );

        $password = password(
            label: 'Password',
            required: true,
            validate: ['password' => 'required|min:8'],
        );

        $user = $createUser->handle(new CreateUserData(
            name: $name,
            email: $email,
            password: $password,
        ));

        $this->components->info("User [{$user->name}] registered successfully with ID [{$user->id}].");

        return self::SUCCESS;
    }
}
