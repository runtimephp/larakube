<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class DeleteNamespace extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $name,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/namespaces/'.rawurlencode($this->name);
    }
}
