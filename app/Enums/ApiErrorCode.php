<?php

declare(strict_types=1);

namespace App\Enums;

enum ApiErrorCode: string
{
    case InvalidCredentials = 'invalid_credentials';
    case Unauthenticated = 'unauthenticated';
    case ValidationFailed = 'validation_failed';
    case NotFound = 'not_found';

    public function httpStatus(): int
    {
        return match ($this) {
            self::InvalidCredentials, self::Unauthenticated => 401,
            self::ValidationFailed => 422,
            self::NotFound => 404,
        };
    }
}
