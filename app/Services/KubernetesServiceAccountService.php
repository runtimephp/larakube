<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceAccountService;
use App\Http\Integrations\Kubernetes\Data\ServiceAccountData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateServiceAccount;

final readonly class KubernetesServiceAccountService implements ServiceAccountService
{
    public function __construct(
        private KubernetesConnector $connector,
    ) {}

    public function create(string $name, string $namespace): ServiceAccountData
    {
        return $this->connector->send(new CreateServiceAccount($name, $namespace))->dtoOrFail();
    }
}
