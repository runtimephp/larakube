<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NamespaceService;
use App\Http\Integrations\Kubernetes\Data\NamespaceData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateNamespace;

final readonly class KubernetesNamespaceService implements NamespaceService
{
    public function __construct(
        private KubernetesConnector $connector,
    ) {}

    public function create(string $name): NamespaceData
    {
        return $this->connector->send(new CreateNamespace($name))->dtoOrFail();
    }
}
