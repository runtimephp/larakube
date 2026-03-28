<?php

declare(strict_types=1);

use App\Data\ApiErrorData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;

test('exception carries api error data', function (): void {
    $errorData = new ApiErrorData(
        message: 'Invalid credentials.',
        code: ApiErrorCode::InvalidCredentials,
    );

    $exception = new LarakubeApiException($errorData);

    expect($exception)
        ->getMessage()->toBe('Invalid credentials.')
        ->getCode()->toBe(401)
        ->errorData->toBe($errorData);
});

test('exception with validation errors', function (): void {
    $errorData = new ApiErrorData(
        message: 'Validation failed.',
        code: ApiErrorCode::ValidationFailed,
        errors: ['email' => ['The email field is required.']],
    );

    $exception = new LarakubeApiException($errorData);

    expect($exception)
        ->getMessage()->toBe('Validation failed.')
        ->getCode()->toBe(422)
        ->errorData->errors->toBe(['email' => ['The email field is required.']]);
});
