<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SessionUserData;
use App\Models\User;
use App\Queries\UserQuery;
use Illuminate\Support\Facades\Hash;
use SensitiveParameter;

final readonly class LoginUser
{
    public function __construct(private UserQuery $userQuery) {}

    public function handle(string $email, #[SensitiveParameter] string $password): ?SessionUserData
    {
        $user = ($this->userQuery)()->byEmail($email)->first();

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
