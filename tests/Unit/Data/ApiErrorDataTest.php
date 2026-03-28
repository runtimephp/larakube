<?php

declare(strict_types=1);

use App\Data\ApiErrorData;
use App\Enums\ApiErrorCode;

test('constructor sets properties', function (): void {
    $data = new ApiErrorData(
        message: 'Invalid credentials.',
        code: ApiErrorCode::InvalidCredentials,
        errors: ['email' => ['The email is invalid.']],
    );

    expect($data)
        ->message->toBe('Invalid credentials.')
        ->code->toBe(ApiErrorCode::InvalidCredentials)
        ->errors->toBe(['email' => ['The email is invalid.']]);
});

test('errors default to empty array', function (): void {
    $data = new ApiErrorData(
        message: 'Not found.',
        code: ApiErrorCode::NotFound,
    );

    expect($data->errors)->toBe([]);
});

test('toArray returns structured response', function (): void {
    $data = new ApiErrorData(
        message: 'Validation failed.',
        code: ApiErrorCode::ValidationFailed,
        errors: ['name' => ['The name field is required.']],
    );

    expect($data->toArray())->toBe([
        'message' => 'Validation failed.',
        'code' => 'validation_failed',
        'errors' => ['name' => ['The name field is required.']],
    ]);
});

test('fromArray reconstructs data', function (): void {
    $array = [
        'message' => 'Invalid credentials.',
        'code' => 'invalid_credentials',
        'errors' => [],
    ];

    $data = ApiErrorData::fromArray($array);

    expect($data)
        ->message->toBe('Invalid credentials.')
        ->code->toBe(ApiErrorCode::InvalidCredentials)
        ->errors->toBe([]);
});
