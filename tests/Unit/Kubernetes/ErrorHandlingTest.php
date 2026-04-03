<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\StatusReason;
use App\Http\Integrations\Kubernetes\Exceptions\KubernetesStatusException;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\NamespaceManifest;
use App\Http\Integrations\Kubernetes\Requests\ApplyManifest;
use App\Http\Integrations\Kubernetes\Requests\GetNamespace;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('throws KubernetesStatusException on 409 AlreadyExists', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        ApplyManifest::class => MockResponse::make([
            'apiVersion' => 'v1',
            'kind' => 'Status',
            'metadata' => [],
            'status' => 'Failure',
            'message' => 'namespaces "kuven-ns" already exists',
            'reason' => 'AlreadyExists',
            'details' => [
                'name' => 'kuven-ns',
                'kind' => 'namespaces',
            ],
            'code' => 409,
        ], 409),
    ]);

    $connector->withMockClient($mockClient);

    $manifest = new NamespaceManifest(
        metadata: new ManifestMetadata(name: 'kuven-ns'),
    );

    expect(fn () => $connector->send(new ApplyManifest($manifest)))
        ->toThrow(function (KubernetesStatusException $e): void {
            expect($e->status->reason)->toBe(StatusReason::AlreadyExists)
                ->and($e->getCode())->toBe(409)
                ->and($e->status->resource)->toBe('namespaces');
        });
});

it('throws KubernetesStatusException on 404 NotFound', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        GetNamespace::class => MockResponse::make([
            'apiVersion' => 'v1',
            'kind' => 'Status',
            'metadata' => [],
            'status' => 'Failure',
            'message' => 'namespaces "missing" not found',
            'reason' => 'NotFound',
            'code' => 404,
        ], 404),
    ]);

    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new GetNamespace('missing')))
        ->toThrow(function (KubernetesStatusException $e): void {
            expect($e->status->reason)->toBe(StatusReason::NotFound)
                ->and($e->getCode())->toBe(404);
        });
});

it('throws generic RequestException on non-Status error body', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        GetNamespace::class => MockResponse::make('Internal Server Error', 500),
    ]);

    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new GetNamespace('test')))
        ->toThrow(RequestException::class);
});

it('throws generic RequestException on JSON error without Status kind', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        GetNamespace::class => MockResponse::make(['error' => 'something went wrong'], 500),
    ]);

    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new GetNamespace('test')))
        ->toThrow(RequestException::class);
});

it('auto-throws on error responses without dtoOrFail', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        GetNamespace::class => MockResponse::make([
            'apiVersion' => 'v1',
            'kind' => 'Status',
            'metadata' => [],
            'status' => 'Failure',
            'message' => 'forbidden',
            'reason' => 'Forbidden',
            'code' => 403,
        ], 403),
    ]);

    $connector->withMockClient($mockClient);

    // send() alone should throw — no dtoOrFail() needed
    expect(fn () => $connector->send(new GetNamespace('test')))
        ->toThrow(KubernetesStatusException::class);
});
