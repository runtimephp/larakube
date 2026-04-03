<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\RoleBindingData;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\RoleBindingManifest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class CreateRoleBinding extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $name,
        private readonly string $namespace,
        private readonly string $roleName,
        private readonly string $serviceAccountName,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/apis/rbac.authorization.k8s.io/v1/namespaces/'.rawurlencode($this->namespace).'/rolebindings';
    }

    public function createDtoFromResponse(Response $response): RoleBindingData
    {
        return RoleBindingData::fromKubernetesResponse($response->json());
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return new RoleBindingManifest(
            metadata: new ManifestMetadata(
                name: $this->name,
                namespace: $this->namespace,
            ),
            roleName: $this->roleName,
            serviceAccountName: $this->serviceAccountName,
        )->toArray();
    }
}
