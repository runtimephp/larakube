<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\PortProtocol;
use App\Http\Integrations\Kubernetes\Manifests\ContainerPort;
use App\Http\Integrations\Kubernetes\Manifests\ContainerSpec;
use App\Http\Integrations\Kubernetes\Manifests\DeploymentManifest;
use App\Http\Integrations\Kubernetes\Manifests\DeploymentSpec;
use App\Http\Integrations\Kubernetes\Manifests\EnvVar;
use App\Http\Integrations\Kubernetes\Manifests\LabelSelector;
use App\Http\Integrations\Kubernetes\Manifests\LabelSet;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\PodSpec;
use App\Http\Integrations\Kubernetes\Manifests\PodTemplateSpec;

it('serializes a deployment manifest', function (): void {
    $manifest = new DeploymentManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-api',
            namespace: 'kuven-test-ns',
            labels: LabelSet::kuvenApp('kuven-api', 'api'),
        ),
        spec: new DeploymentSpec(
            replicas: 2,
            selector: new LabelSelector(
                matchLabels: LabelSet::kuvenApp('kuven-api', 'api'),
            ),
            template: new PodTemplateSpec(
                metadata: new ManifestMetadata(
                    name: 'kuven-api',
                    labels: LabelSet::kuvenApp('kuven-api', 'api'),
                ),
                spec: new PodSpec(
                    containers: [
                        new ContainerSpec(
                            name: 'api',
                            image: 'ghcr.io/getkuven/api:1.2.3',
                            ports: [
                                new ContainerPort(
                                    containerPort: 8080,
                                    protocol: PortProtocol::Udp,
                                    name: 'http',
                                ),
                            ],
                            env: [
                                new EnvVar(
                                    name: 'APP_ENV',
                                    value: 'production',
                                ),
                            ],
                        ),
                    ],
                    serviceAccountName: 'kuven-operator',
                ),
            ),
        ),
    );

    expect($manifest->toArray())->toBe([
        'apiVersion' => 'apps/v1',
        'kind' => 'Deployment',
        'metadata' => [
            'name' => 'kuven-api',
            'namespace' => 'kuven-test-ns',
            'labels' => [
                'app.kubernetes.io/name' => 'kuven-api',
                'app.kubernetes.io/component' => 'api',
                'app.kubernetes.io/managed-by' => 'kuven',
                'app.kubernetes.io/part-of' => 'kuven',
            ],
        ],
        'spec' => [
            'replicas' => 2,
            'selector' => [
                'matchLabels' => [
                    'app.kubernetes.io/name' => 'kuven-api',
                    'app.kubernetes.io/component' => 'api',
                    'app.kubernetes.io/managed-by' => 'kuven',
                    'app.kubernetes.io/part-of' => 'kuven',
                ],
            ],
            'template' => [
                'metadata' => [
                    'name' => 'kuven-api',
                    'labels' => [
                        'app.kubernetes.io/name' => 'kuven-api',
                        'app.kubernetes.io/component' => 'api',
                        'app.kubernetes.io/managed-by' => 'kuven',
                        'app.kubernetes.io/part-of' => 'kuven',
                    ],
                ],
                'spec' => [
                    'containers' => [
                        [
                            'name' => 'api',
                            'image' => 'ghcr.io/getkuven/api:1.2.3',
                            'ports' => [
                                [
                                    'containerPort' => 8080,
                                    'protocol' => 'UDP',
                                    'name' => 'http',
                                ],
                            ],
                            'env' => [
                                [
                                    'name' => 'APP_ENV',
                                    'value' => 'production',
                                ],
                            ],
                        ],
                    ],
                    'serviceAccountName' => 'kuven-operator',
                ],
            ],
        ],
    ]);
});

it('exposes deployment manifest routing metadata', function (): void {
    $manifest = new DeploymentManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-api',
            namespace: 'kuven-test-ns',
        ),
        spec: new DeploymentSpec(
            replicas: 1,
            selector: new LabelSelector(
                matchLabels: LabelSet::kuvenApp('kuven-api', 'api'),
            ),
            template: new PodTemplateSpec(
                metadata: new ManifestMetadata(
                    name: 'kuven-api',
                    labels: LabelSet::kuvenApp('kuven-api', 'api'),
                ),
                spec: new PodSpec(
                    containers: [
                        new ContainerSpec(
                            name: 'api',
                            image: 'ghcr.io/getkuven/api:1.2.3',
                        ),
                    ],
                ),
            ),
        ),
    );

    expect($manifest->apiVersion()->value)->toBe('apps/v1')
        ->and($manifest->kind()->value)->toBe('Deployment')
        ->and($manifest->resource())->toBe('deployments')
        ->and($manifest->namespace())->toBe('kuven-test-ns')
        ->and($manifest->isClusterScoped())->toBeFalse();
});

it('requires namespace for deployment manifests', function (): void {
    expect(fn (): DeploymentManifest => new DeploymentManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-api',
        ),
        spec: new DeploymentSpec(
            replicas: 1,
            selector: new LabelSelector(
                matchLabels: LabelSet::kuvenApp('kuven-api', 'api'),
            ),
            template: new PodTemplateSpec(
                metadata: new ManifestMetadata(
                    name: 'kuven-api',
                    labels: LabelSet::kuvenApp('kuven-api', 'api'),
                ),
                spec: new PodSpec(
                    containers: [
                        new ContainerSpec(
                            name: 'api',
                            image: 'ghcr.io/getkuven/api:1.2.3',
                        ),
                    ],
                ),
            ),
        ),
    ))->toThrow(InvalidArgumentException::class, 'Deployment manifests require a namespace.');
});

it('requires selector labels to match template metadata labels', function (): void {
    expect(fn (): DeploymentSpec => new DeploymentSpec(
        replicas: 1,
        selector: new LabelSelector(
            matchLabels: new LabelSet([
                'app.kubernetes.io/name' => 'kuven-api',
                'kuven.io/organization' => 'org-123',
            ]),
        ),
        template: new PodTemplateSpec(
            metadata: new ManifestMetadata(
                name: 'kuven-api',
                labels: LabelSet::kuvenApp('kuven-api', 'api'),
            ),
            spec: new PodSpec(
                containers: [
                    new ContainerSpec(
                        name: 'api',
                        image: 'ghcr.io/getkuven/api:1.2.3',
                    ),
                ],
            ),
        ),
    ))->toThrow(InvalidArgumentException::class, 'Deployment selector labels must match template metadata labels.');
});
