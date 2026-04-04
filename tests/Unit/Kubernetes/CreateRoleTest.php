<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\RoleData;
use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\Enums\RbacApiGroup;
use App\Http\Integrations\Kubernetes\Enums\RbacResource;
use App\Http\Integrations\Kubernetes\Enums\RbacVerb;
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
            apiGroups: [RbacApiGroup::CapiCore, RbacApiGroup::CapiInfrastructure, RbacApiGroup::CapiBootstrap, RbacApiGroup::CapiControlPlane],
            resources: [RbacResource::All],
            verbs: [RbacVerb::All],
        ),
        new RuleData(
            apiGroups: [RbacApiGroup::Core],
            resources: [RbacResource::Secrets, RbacResource::ConfigMaps],
            verbs: [RbacVerb::All],
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
        ->and($data->rules[0]->apiGroups)->toContain(RbacApiGroup::CapiCore)
        ->and($data->rules[0]->resources)->toBe([RbacResource::All])
        ->and($data->rules[0]->verbs)->toBe([RbacVerb::All]);

    expect($data->metadata)
        ->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('kuven-operator')
        ->and($data->metadata->namespace)->toBe('kuven-test-ns');
});
