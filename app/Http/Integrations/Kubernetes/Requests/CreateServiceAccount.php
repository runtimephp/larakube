<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\ServiceAccountData;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ServiceAccountManifest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class CreateServiceAccount extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $name,
        private readonly string $namespace,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/namespaces/'.rawurlencode($this->namespace).'/serviceaccounts';
    }

    public function createDtoFromResponse(Response $response): ServiceAccountData
    {
        return ServiceAccountData::fromKubernetesResponse($response->json());
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return new ServiceAccountManifest(
            metadata: new ManifestMetadata(
                name: $this->name,
                namespace: $this->namespace,
            ),
        )->toArray();
    }
}
