<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\RoleData;
use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\RoleManifest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class CreateRole extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  list<RuleData>  $rules
     */
    public function __construct(
        private readonly string $name,
        private readonly string $namespace,
        private readonly array $rules,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/apis/rbac.authorization.k8s.io/v1/namespaces/'.rawurlencode($this->namespace).'/roles';
    }

    public function createDtoFromResponse(Response $response): RoleData
    {
        return RoleData::fromKubernetesResponse($response->json());
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return new RoleManifest(
            metadata: new ManifestMetadata(
                name: $this->name,
                namespace: $this->namespace,
            ),
            rules: $this->rules,
        )->toArray();
    }
}
