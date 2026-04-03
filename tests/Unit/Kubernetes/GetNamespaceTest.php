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
        ->and($data->phase)->toBe('Active')
        ->and($data->metadata)->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('kuven-test-ns')
        ->and($data->metadata->uid)->toBeString()
        ->and(mb_strlen((string) $data->metadata->uid))->toBeGreaterThan(0)
        ->and($data->metadata->resourceVersion)->toBeString()
        ->and($data->metadata->creationTimestamp)->toBeInstanceOf(CarbonImmutable::class);
});
