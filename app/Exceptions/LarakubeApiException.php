<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Data\ApiErrorData;
use RuntimeException;

final class LarakubeApiException extends RuntimeException
{
    public function __construct(public readonly ApiErrorData $errorData)
    {
        parent::__construct(
            message: $errorData->message,
            code: $errorData->code->httpStatus(),
        );
    }
}
