<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateUser;
use App\Data\CreateUserData;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

final class RegisterController
{
    public function store(RegisterRequest $request, CreateUser $createUser): JsonResponse
    {
        $user = $createUser->handle(new CreateUserData(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        ));

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }
}
