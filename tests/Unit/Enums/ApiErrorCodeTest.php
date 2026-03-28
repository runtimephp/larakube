<?php

declare(strict_types=1);

use App\Enums\ApiErrorCode;

test('invalid credentials maps to 401', function (): void {
    expect(ApiErrorCode::InvalidCredentials->httpStatus())->toBe(401);
});

test('unauthenticated maps to 401', function (): void {
    expect(ApiErrorCode::Unauthenticated->httpStatus())->toBe(401);
});

test('validation failed maps to 422', function (): void {
    expect(ApiErrorCode::ValidationFailed->httpStatus())->toBe(422);
});

test('not found maps to 404', function (): void {
    expect(ApiErrorCode::NotFound->httpStatus())->toBe(404);
});

test('all cases have string values', function (): void {
    foreach (ApiErrorCode::cases() as $case) {
        expect($case->value)->toBeString()
            ->and($case->httpStatus())->toBeInt()->toBeGreaterThanOrEqual(400);
    }
});
