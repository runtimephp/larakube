<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\NamespaceData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\GetNamespace;
use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('gets a namespace from the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        GetNamespace::class => MockResponse::fixture('kubernetes/get-namespace'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetNamespace('kuven-test-ns'));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(NamespaceData::class)
        ->phase->toBe('Active');

    expect($data->metadata)
        ->toBeInstanceOf(ResourceMetadata::class)
        ->name->toBe('kuven-test-ns')
        ->uid->toBeString()->not->toBeEmpty()
        ->resourceVersion->toBeString()
        ->creationTimestamp->toBeInstanceOf(CarbonImmutable::class);
});
