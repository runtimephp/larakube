<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\Enums\KuvenLabel;
use App\Http\Integrations\Kubernetes\Enums\SecretType;
use App\Http\Integrations\Kubernetes\Manifests\AnnotationSet;
use App\Http\Integrations\Kubernetes\Manifests\LabelSet;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\NamespaceManifest;
use App\Http\Integrations\Kubernetes\Manifests\RoleBindingManifest;
use App\Http\Integrations\Kubernetes\Manifests\RoleManifest;
use App\Http\Integrations\Kubernetes\Manifests\SecretManifest;
use App\Http\Integrations\Kubernetes\Manifests\SecretStringData;
use App\Http\Integrations\Kubernetes\Manifests\ServiceAccountManifest;

it('serializes a namespace manifest', function (): void {
    $manifest = new NamespaceManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-test-ns',
            labels: new LabelSet([
                'app.kubernetes.io/managed-by' => 'kuven',
            ]),
            annotations: new AnnotationSet([
                'kuven.io/environment' => 'test',
            ]),
        ),
    );

    expect($manifest->toArray())->toBe([
        'apiVersion' => 'v1',
        'kind' => 'Namespace',
        'metadata' => [
            'name' => 'kuven-test-ns',
            'labels' => [
                'app.kubernetes.io/managed-by' => 'kuven',
            ],
            'annotations' => [
                'kuven.io/environment' => 'test',
            ],
        ],
    ]);
});

it('serializes a service account manifest', function (): void {
    $manifest = new ServiceAccountManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-operator',
            namespace: 'kuven-test-ns',
        ),
    );

    expect($manifest->toArray())->toBe([
        'apiVersion' => 'v1',
        'kind' => 'ServiceAccount',
        'metadata' => [
            'name' => 'kuven-operator',
            'namespace' => 'kuven-test-ns',
        ],
    ]);
});

it('exposes service account manifest routing metadata', function (): void {
    $manifest = new ServiceAccountManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-operator',
            namespace: 'kuven-test-ns',
        ),
    );

    expect($manifest->apiVersion()->value)->toBe('v1')
        ->and($manifest->kind()->value)->toBe('ServiceAccount')
        ->and($manifest->resource())->toBe('serviceaccounts')
        ->and($manifest->namespace())->toBe('kuven-test-ns')
        ->and($manifest->isClusterScoped())->toBeFalse();
});

it('serializes a secret manifest with encoded data', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(
            name: 'hetzner-credentials',
            namespace: 'kuven-test-ns',
        ),
        data: new SecretStringData([
            'token' => 'hcloud-api-token-value',
        ]),
        type: SecretType::Opaque,
    );

    expect($manifest->toArray())->toBe([
        'apiVersion' => 'v1',
        'kind' => 'Secret',
        'metadata' => [
            'name' => 'hetzner-credentials',
            'namespace' => 'kuven-test-ns',
        ],
        'type' => 'Opaque',
        'data' => [
            'token' => base64_encode('hcloud-api-token-value'),
        ],
    ]);
});

it('exposes secret manifest routing metadata', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(
            name: 'hetzner-credentials',
            namespace: 'kuven-test-ns',
        ),
        data: new SecretStringData([
            'token' => 'hcloud-api-token-value',
        ]),
    );

    expect($manifest->apiVersion()->value)->toBe('v1')
        ->and($manifest->kind()->value)->toBe('Secret')
        ->and($manifest->resource())->toBe('secrets')
        ->and($manifest->namespace())->toBe('kuven-test-ns')
        ->and($manifest->isClusterScoped())->toBeFalse();
});

it('supports custom secret type strings', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(
            name: 'tls-cert',
            namespace: 'kuven-test-ns',
        ),
        data: new SecretStringData([
            'tls.crt' => 'cert',
            'tls.key' => 'key',
        ]),
        type: 'custom.type/v1',
    );

    expect($manifest->toArray()['type'])->toBe('custom.type/v1');
});

it('rejects a secret manifest without a namespace', function (): void {
    expect(fn (): SecretManifest => new SecretManifest(
        metadata: new ManifestMetadata(name: 'my-secret'),
        data: new SecretStringData(['key' => 'value']),
    ))->toThrow(InvalidArgumentException::class, 'Secret manifests require a namespace.');
});

it('rejects a service account manifest without a namespace', function (): void {
    expect(fn (): ServiceAccountManifest => new ServiceAccountManifest(
        metadata: new ManifestMetadata(name: 'my-sa'),
    ))->toThrow(InvalidArgumentException::class, 'ServiceAccount manifests require a namespace.');
});

it('builds a kuven app label set', function (): void {
    $labels = LabelSet::kuvenApp(name: 'kuven-api', component: 'api')
        ->with(KuvenLabel::Organization, 'org-123')
        ->with('custom.label/key', 'custom-value');

    expect($labels->toArray())->toBe([
        'app.kubernetes.io/name' => 'kuven-api',
        'app.kubernetes.io/component' => 'api',
        'app.kubernetes.io/managed-by' => 'kuven',
        'app.kubernetes.io/part-of' => 'kuven',
        'kuven.io/organization' => 'org-123',
        'custom.label/key' => 'custom-value',
    ]);
});

it('serializes a role manifest', function (): void {
    $manifest = new RoleManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-operator',
            namespace: 'kuven-test-ns',
        ),
        rules: [
            new RuleData(
                apiGroups: ['cluster.x-k8s.io'],
                resources: ['*'],
                verbs: ['*'],
            ),
        ],
    );

    expect($manifest->toArray())->toBe([
        'apiVersion' => 'rbac.authorization.k8s.io/v1',
        'kind' => 'Role',
        'metadata' => [
            'name' => 'kuven-operator',
            'namespace' => 'kuven-test-ns',
        ],
        'rules' => [
            [
                'apiGroups' => ['cluster.x-k8s.io'],
                'resources' => ['*'],
                'verbs' => ['*'],
            ],
        ],
    ]);
});

it('exposes role manifest routing metadata', function (): void {
    $manifest = new RoleManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-operator',
            namespace: 'kuven-test-ns',
        ),
        rules: [],
    );

    expect($manifest->apiVersion()->value)->toBe('rbac.authorization.k8s.io/v1')
        ->and($manifest->kind()->value)->toBe('Role')
        ->and($manifest->resource())->toBe('roles')
        ->and($manifest->namespace())->toBe('kuven-test-ns')
        ->and($manifest->isClusterScoped())->toBeFalse();
});

it('rejects a role manifest without a namespace', function (): void {
    expect(fn (): RoleManifest => new RoleManifest(
        metadata: new ManifestMetadata(name: 'kuven-operator'),
        rules: [],
    ))->toThrow(InvalidArgumentException::class, 'Role manifests require a namespace.');
});

it('serializes a role binding manifest', function (): void {
    $manifest = new RoleBindingManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-operator',
            namespace: 'kuven-test-ns',
        ),
        roleName: 'kuven-operator',
        serviceAccountName: 'kuven-operator',
    );

    expect($manifest->toArray())->toBe([
        'apiVersion' => 'rbac.authorization.k8s.io/v1',
        'kind' => 'RoleBinding',
        'metadata' => [
            'name' => 'kuven-operator',
            'namespace' => 'kuven-test-ns',
        ],
        'roleRef' => [
            'apiGroup' => 'rbac.authorization.k8s.io',
            'kind' => 'Role',
            'name' => 'kuven-operator',
        ],
        'subjects' => [
            [
                'kind' => 'ServiceAccount',
                'name' => 'kuven-operator',
                'namespace' => 'kuven-test-ns',
            ],
        ],
    ]);
});

it('exposes role binding manifest routing metadata', function (): void {
    $manifest = new RoleBindingManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-operator',
            namespace: 'kuven-test-ns',
        ),
        roleName: 'kuven-operator',
        serviceAccountName: 'kuven-operator',
    );

    expect($manifest->apiVersion()->value)->toBe('rbac.authorization.k8s.io/v1')
        ->and($manifest->kind()->value)->toBe('RoleBinding')
        ->and($manifest->resource())->toBe('rolebindings')
        ->and($manifest->namespace())->toBe('kuven-test-ns')
        ->and($manifest->isClusterScoped())->toBeFalse();
});

it('rejects a role binding manifest without a namespace', function (): void {
    expect(fn (): RoleBindingManifest => new RoleBindingManifest(
        metadata: new ManifestMetadata(name: 'kuven-operator'),
        roleName: 'kuven-operator',
        serviceAccountName: 'kuven-operator',
    ))->toThrow(InvalidArgumentException::class, 'RoleBinding manifests require a namespace.');
});
