<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\RoleBindingData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateRoleBinding;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates a role binding on the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        CreateRoleBinding::class => MockResponse::fixture('kubernetes/create-role-binding'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new CreateRoleBinding(
        name: 'kuven-operator',
        namespace: 'kuven-test-ns',
        roleName: 'kuven-operator',
        serviceAccountName: 'kuven-operator',
    ));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(RoleBindingData::class)
        ->and($data->roleName)->toBe('kuven-operator')
        ->and($data->subjects)->toHaveCount(1);

    expect($data->subjects[0])
        ->toBe([
            'kind' => 'ServiceAccount',
            'name' => 'kuven-operator',
            'namespace' => 'kuven-test-ns',
        ]);

    expect($data->metadata)
        ->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('kuven-operator')
        ->and($data->metadata->namespace)->toBe('kuven-test-ns');
});
