<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Requests;

use App\Http\Integrations\Kubernetes\Data\SecretData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use SensitiveParameter;

final class CreateSecret extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        private readonly string $name,
        private readonly string $namespace,
        #[SensitiveParameter] private readonly array $data,
        private readonly string $type = 'Opaque',
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/namespaces/'.rawurlencode($this->namespace).'/secrets';
    }

    public function createDtoFromResponse(Response $response): SecretData
    {
        return SecretData::fromKubernetesResponse($response->json());
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        $encodedData = [];

        foreach ($this->data as $key => $value) {
            $encodedData[$key] = base64_encode($value);
        }

        return [
            'apiVersion' => 'v1',
            'kind' => 'Secret',
            'metadata' => [
                'name' => $this->name,
                'namespace' => $this->namespace,
            ],
            'type' => $this->type,
            'data' => $encodedData,
        ];
    }
}
