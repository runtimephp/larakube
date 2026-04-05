<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new ClusterManifest(
            metadata: new ManifestMetadata(name: 'my-cluster', namespace: 'kuven-org-123'),
            spec: new ClusterSpec(
                controlPlaneRef: new ObjectReference(ApiVersion::CapiControlPlaneV1Beta1, Kind::KubeadmControlPlane, 'my-cp'),
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerCluster, 'my-cluster'),
            ),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiCoreV1Beta1)
            ->and($manifest->kind())->toBe(Kind::Cluster)
            ->and($manifest->resource())->toBe('clusters')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['controlPlaneRef']['kind'])->toBe('KubeadmControlPlane')
            ->and($manifest->toArray()['spec']['infrastructureRef']['kind'])->toBe('DockerCluster');
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new ClusterManifest(
            metadata: new ManifestMetadata(name: 'my-cluster'),
            spec: new ClusterSpec(
                controlPlaneRef: new ObjectReference(ApiVersion::CapiControlPlaneV1Beta1, Kind::KubeadmControlPlane, 'cp'),
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerCluster, 'dc'),
            ),
        ))->toThrow(InvalidArgumentException::class);
    });
