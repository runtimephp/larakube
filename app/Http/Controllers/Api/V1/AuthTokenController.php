<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\LoginUser;
use App\Enums\ApiErrorCode;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\AuthTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class AuthTokenController
{
    public function store(LoginRequest $request, LoginUser $loginUser): AuthTokenResource|JsonResponse
    {
        $userData = $loginUser->handle(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        if ($userData === null) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'code' => ApiErrorCode::InvalidCredentials->value,
                'errors' => [],
            ], ApiErrorCode::InvalidCredentials->httpStatus());
        }

        return new AuthTokenResource($userData);
    }

    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
