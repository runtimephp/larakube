<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\StatusReason;
use App\Http\Integrations\Kubernetes\Exceptions\KubernetesStatusException;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\DeleteNamespace;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('deletes a namespace from the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        DeleteNamespace::class => MockResponse::fixture('kubernetes/delete-namespace'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new DeleteNamespace('kuven-test-ns'));

    expect($response->successful())->toBeTrue();
    expect($response->json('status.phase'))->toBe('Terminating');
});

it('resolves the correct endpoint', function (): void {
    $request = new DeleteNamespace('kuven-test-ns');

    expect($request->resolveEndpoint())->toBe('/api/v1/namespaces/kuven-test-ns');
});

it('throws KubernetesStatusException when namespace not found', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        DeleteNamespace::class => MockResponse::make([
            'apiVersion' => 'v1',
            'kind' => 'Status',
            'metadata' => [],
            'status' => 'Failure',
            'message' => 'namespaces "missing-ns" not found',
            'reason' => 'NotFound',
            'code' => 404,
        ], 404),
    ]);

    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new DeleteNamespace('missing-ns')))
        ->toThrow(function (KubernetesStatusException $e): void {
            expect($e->status->reason)->toBe(StatusReason::NotFound)
                ->and($e->getCode())->toBe(404);
        });
});
