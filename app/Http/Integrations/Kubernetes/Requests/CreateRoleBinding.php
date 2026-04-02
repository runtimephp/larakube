<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\RoleBindingData;
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
        return [
            'apiVersion' => 'rbac.authorization.k8s.io/v1',
            'kind' => 'RoleBinding',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
            ],
            'roleRef' => [
                'apiGroup' => 'rbac.authorization.k8s.io',
                'kind' => 'Role',
                'name' => $this->roleName,
            ],
            'subjects' => [
                [
                    'kind' => 'ServiceAccount',
                    'name' => $this->serviceAccountName,
                    'namespace' => $this->namespace,
                ],
            ],
        ];
    }
}
