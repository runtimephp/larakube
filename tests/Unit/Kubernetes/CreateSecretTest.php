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
        ->and($data->type)->toBe('Opaque')
        ->and($data->data)->toBeArray()->toHaveKey('token')
        ->and($data->metadata)->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('hetzner-credentials')
        ->and($data->metadata->namespace)->toBe('kuven-test-ns')
        ->and($data->metadata->uid)->toBeString()
        ->and(mb_strlen((string) $data->metadata->uid))->toBeGreaterThan(0)
        ->and($data->metadata->creationTimestamp)->toBeInstanceOf(CarbonImmutable::class);
});

it('marks secret data as sensitive parameter', function (): void {
    $param = new ReflectionParameter([SecretData::class, '__construct'], 'data');

    expect($param->getAttributes(SensitiveParameter::class))->toHaveCount(1);
});
