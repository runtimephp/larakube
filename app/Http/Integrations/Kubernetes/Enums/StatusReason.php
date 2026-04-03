<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum StatusReason: string
{
    case AlreadyExists = 'AlreadyExists';
    case BadRequest = 'BadRequest';
    case Conflict = 'Conflict';
    case Forbidden = 'Forbidden';
    case Gone = 'Gone';
    case InternalError = 'InternalError';
    case Invalid = 'Invalid';
    case MethodNotAllowed = 'MethodNotAllowed';
    case NotFound = 'NotFound';
    case ServerTimeout = 'ServerTimeout';
    case ServiceUnavailable = 'ServiceUnavailable';
    case TooManyRequests = 'TooManyRequests';
    case Unauthorized = 'Unauthorized';
    case Unknown = 'Unknown';
}
