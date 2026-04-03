<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\ServiceAccountData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateServiceAccount;
use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates a service account on the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        CreateServiceAccount::class => MockResponse::fixture('kubernetes/create-service-account'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new CreateServiceAccount(
        name: 'kuven-operator',
        namespace: 'kuven-test-ns',
    ));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(ServiceAccountData::class)
        ->and($data->metadata)->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('kuven-operator')
        ->and($data->metadata->namespace)->toBe('kuven-test-ns')
        ->and($data->metadata->uid)->toBeString()
        ->and(mb_strlen((string) $data->metadata->uid))->toBeGreaterThan(0)
        ->and($data->metadata->creationTimestamp)->toBeInstanceOf(CarbonImmutable::class);
});
