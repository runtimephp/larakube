<?php

declare(strict_types=1);

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
