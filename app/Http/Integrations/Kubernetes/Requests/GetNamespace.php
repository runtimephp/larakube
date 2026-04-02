<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\NamespaceData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetNamespace extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $name,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/namespaces/{$this->name}";
    }

    public function createDtoFromResponse(Response $response): NamespaceData
    {
        return NamespaceData::fromKubernetesResponse($response->json());
    }
}
