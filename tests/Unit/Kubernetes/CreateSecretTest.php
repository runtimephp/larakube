<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\SecretData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateSecret;
use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates a secret on the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        CreateSecret::class => MockResponse::fixture('kubernetes/create-secret'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new CreateSecret(
        name: 'hetzner-credentials',
        namespace: 'kuven-test-ns',
        data: ['token' => 'hcloud-api-token-value'],
    ));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(SecretData::class)
        ->type->toBe('Opaque')
        ->data->toBeArray()->toHaveKey('token');

    expect($data->metadata)
        ->toBeInstanceOf(ResourceMetadata::class)
        ->name->toBe('hetzner-credentials')
        ->namespace->toBe('kuven-test-ns')
        ->uid->toBeString()->not->toBeEmpty()
        ->creationTimestamp->toBeInstanceOf(CarbonImmutable::class);
});
