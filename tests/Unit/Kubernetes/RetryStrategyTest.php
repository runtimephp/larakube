<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Exceptions\KubernetesStatusException;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\GetNamespace;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

test('retries on 429 and succeeds on next attempt', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Too Many Requests'], 429),
        MockResponse::fixture('kubernetes/get-namespace'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetNamespace('kuven-test-ns'));

    expect($response->successful())->toBeTrue();
    $mockClient->assertSentCount(2);
});

test('retries on 503 and succeeds on next attempt', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Service Unavailable'], 503),
        MockResponse::fixture('kubernetes/get-namespace'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetNamespace('kuven-test-ns'));

    expect($response->successful())->toBeTrue();
    $mockClient->assertSentCount(2);
});

test('does not retry on 409 conflict', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        MockResponse::make([
            'apiVersion' => 'v1',
            'kind' => 'Status',
            'metadata' => [],
            'status' => 'Failure',
            'message' => 'already exists',
            'reason' => 'AlreadyExists',
            'code' => 409,
        ], 409),
    ]);

    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new GetNamespace('test')))
        ->toThrow(KubernetesStatusException::class);

    $mockClient->assertSentCount(1);
});

test('throws after max retries exhausted', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Too Many Requests'], 429),
        MockResponse::make(['message' => 'Too Many Requests'], 429),
        MockResponse::make(['message' => 'Too Many Requests'], 429),
    ]);

    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new GetNamespace('test')))
        ->toThrow(RequestException::class);

    $mockClient->assertSentCount(3);
});

test('retries on connection failure', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $exception = new FatalRequestException(new Exception('Connection refused'), $connector->createPendingRequest(new GetNamespace('test')));

    expect($connector->handleRetry($exception, new GetNamespace('test')))->toBeTrue();
});
