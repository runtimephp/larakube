<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateUserData;
use App\Models\User;

final class CreateUser
{
    public function handle(CreateUserData $createUserData): User
    {
        return User::query()->create([
            'name' => $createUserData->name,
            'email' => $createUserData->email,
            'password' => $createUserData->password,
        ]);
    }
}
