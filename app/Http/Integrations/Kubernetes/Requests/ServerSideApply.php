<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class ServerSideApply extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    /**
     * @param  string  $fieldManager  The field manager identifier for server-side apply.
     * @param  bool|null  $force  When true, force-apply to resolve field ownership conflicts.
     */
    public function __construct(
        private readonly ManifestContract $manifest,
        private readonly string $fieldManager = 'kuven',
        private readonly ?bool $force = null,
    ) {}

    public function resolveEndpoint(): string
    {
        $apiVersion = $this->manifest->apiVersion()->value;
        $resource = $this->manifest->resource();
        $name = $this->manifest->toArray()['metadata']['name'];

        if (str_contains($apiVersion, '/')) {
            [$group, $version] = explode('/', $apiVersion, 2);
            $base = "/apis/{$group}/{$version}";
        } else {
            $base = "/api/{$apiVersion}";
        }

        if ($this->manifest->isClusterScoped()) {
            return "{$base}/".rawurlencode($resource).'/'.rawurlencode($name);
        }

        $namespace = $this->manifest->namespace();

        return "{$base}/namespaces/".rawurlencode((string) $namespace).'/'.rawurlencode($resource).'/'.rawurlencode($name);
    }

    public function createDtoFromResponse(Response $response): ManifestData
    {
        return ManifestData::fromKubernetesResponse($response->json());
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/apply-patch+yaml',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        $query = [
            'fieldManager' => $this->fieldManager,
        ];

        if ($this->force === true) {
            $query['force'] = 'true';
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->manifest->toArray();
    }
}
