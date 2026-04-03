<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use InvalidArgumentException;

final readonly class RoleBindingManifest implements ManifestContract
{
    public function __construct(
        public ManifestMetadata $metadata,
        public string $roleName,
        public string $serviceAccountName,
    ) {
        if ($this->metadata->namespace === null || mb_trim($this->metadata->namespace) === '') {
            throw new InvalidArgumentException('RoleBinding manifests require a namespace.');
        }
    }

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::RbacAuthorizationV1;
    }

    public function kind(): Kind
    {
        return Kind::RoleBinding;
    }

    public function resource(): string
    {
        return 'rolebindings';
    }

    public function namespace(): string
    {
        return $this->metadata->namespace;
    }

    public function isClusterScoped(): bool
    {
        return false;
    }

    /**
     * @return array{apiVersion: string, kind: string, metadata: array{name: string, namespace: string, labels?: array<string, string>, annotations?: array<string, string>}, roleRef: array{apiGroup: string, kind: string, name: string}, subjects: list<array{kind: string, name: string, namespace: string}>}
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion()->value,
            'kind' => $this->kind()->value,
            'metadata' => $this->metadata->toArray(),
            'roleRef' => [
                'apiGroup' => 'rbac.authorization.k8s.io',
                'kind' => 'Role',
                'name' => $this->roleName,
            ],
            'subjects' => [
                [
                    'kind' => 'ServiceAccount',
                    'name' => $this->serviceAccountName,
                    'namespace' => $this->metadata->namespace,
                ],
            ],
        ];
    }
}
