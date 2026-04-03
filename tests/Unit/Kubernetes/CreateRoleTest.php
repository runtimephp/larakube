<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\RoleData;
use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateRole;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates a role on the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        CreateRole::class => MockResponse::fixture('kubernetes/create-role'),
    ]);

    $connector->withMockClient($mockClient);

    $rules = [
        new RuleData(
            apiGroups: ['cluster.x-k8s.io', 'infrastructure.cluster.x-k8s.io', 'bootstrap.cluster.x-k8s.io', 'controlplane.cluster.x-k8s.io'],
            resources: ['*'],
            verbs: ['*'],
        ),
        new RuleData(
            apiGroups: [''],
            resources: ['secrets', 'configmaps'],
            verbs: ['*'],
        ),
    ];

    $response = $connector->send(new CreateRole(
        name: 'kuven-operator',
        namespace: 'kuven-test-ns',
        rules: $rules,
    ));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(RoleData::class)
        ->and($data->rules)->toHaveCount(2);

    expect($data->rules[0])
        ->toBeInstanceOf(RuleData::class)
        ->and($data->rules[0]->apiGroups)->toContain('cluster.x-k8s.io')
        ->and($data->rules[0]->resources)->toBe(['*'])
        ->and($data->rules[0]->verbs)->toBe(['*']);

    expect($data->metadata)
        ->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('kuven-operator')
        ->and($data->metadata->namespace)->toBe('kuven-test-ns');
});
