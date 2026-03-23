<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SessionUserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use SensitiveParameter;

final class LoginUser
{
    public function handle(string $email, #[SensitiveParameter] string $password): ?SessionUserData
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User || ! Hash::check($password, $user->password)) {
            return null;
        }

        $token = $user->createToken('cli')->plainTextToken;

        return new SessionUserData(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            token: $token,
        );
    }
}
