<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\ManifestData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class ApplyManifest extends Request implements HasBody
{
    use HasJsonBody;

    private const CLUSTER_SCOPED_KINDS = [
        'Namespace',
        'ClusterRole',
        'ClusterRoleBinding',
        'Node',
        'PersistentVolume',
    ];

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $manifest
     */
    public function __construct(
        private readonly array $manifest,
    ) {}

    public function resolveEndpoint(): string
    {
        $apiVersion = $this->manifest['apiVersion'];
        $kind = $this->manifest['kind'];
        $namespace = $this->manifest['metadata']['namespace'] ?? null;
        $resource = mb_strtolower((string) $kind).'s';

        $isClusterScoped = in_array($kind, self::CLUSTER_SCOPED_KINDS, true) || $namespace === null;

        if (str_contains((string) $apiVersion, '/')) {
            [$group, $version] = explode('/', (string) $apiVersion, 2);
            $base = "/apis/{$group}/{$version}";
        } else {
            $base = "/api/{$apiVersion}";
        }

        if ($isClusterScoped) {
            return "{$base}/{$resource}";
        }

        return "{$base}/namespaces/{$namespace}/{$resource}";
    }

    public function createDtoFromResponse(Response $response): ManifestData
    {
        return ManifestData::fromKubernetesResponse($response->json());
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->manifest;
    }
}
