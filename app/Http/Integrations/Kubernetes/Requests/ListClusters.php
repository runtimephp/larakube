<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\ClusterData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class ListClusters extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $namespace,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/apis/cluster.x-k8s.io/v1beta2/namespaces/'.rawurlencode($this->namespace).'/clusters';
    }

    /**
     * @return list<ClusterData>
     */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(
            ClusterData::fromKubernetesResponse(...),
            $response->json('items', []),
        );
    }
}
