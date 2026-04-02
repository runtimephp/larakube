<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\NamespaceData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateNamespace;
use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates a namespace on the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        CreateNamespace::class => MockResponse::fixture('kubernetes/create-namespace'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new CreateNamespace('kuven-test-ns'));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(NamespaceData::class)
        ->phase->toBe('Active');

    expect($data->metadata)
        ->toBeInstanceOf(ResourceMetadata::class)
        ->name->toBe('kuven-test-ns')
        ->uid->toBeString()->not->toBeEmpty()
        ->resourceVersion->toBeString()
        ->creationTimestamp->toBeInstanceOf(CarbonImmutable::class)
        ->namespace->toBeNull()
        ->labels->toBeArray()->toHaveKey('kubernetes.io/metadata.name')
        ->annotations->toBeArray();
});
