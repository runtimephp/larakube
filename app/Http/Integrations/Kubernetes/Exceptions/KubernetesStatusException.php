<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Exceptions;

use App\Http\Integrations\Kubernetes\Data\StatusData;
use RuntimeException;

final class KubernetesStatusException extends RuntimeException
{
    public function __construct(
        public readonly StatusData $status,
    ) {
        parent::__construct($status->message, $status->code);
    }
}
