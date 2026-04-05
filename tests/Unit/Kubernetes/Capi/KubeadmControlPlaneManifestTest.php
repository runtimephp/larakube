<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new KubeadmControlPlaneManifest(
            metadata: new ManifestMetadata(name: 'my-cp', namespace: 'kuven-org-123'),
            spec: new KubeadmControlPlaneSpec(
                replicas: 3,
                version: 'v1.30.2',
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'my-cp'),
            ),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiControlPlaneV1Beta1)
            ->and($manifest->kind())->toBe(Kind::KubeadmControlPlane)
            ->and($manifest->resource())->toBe('kubeadmcontrolplanes')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['replicas'])->toBe(3)
            ->and($manifest->toArray()['spec']['version'])->toBe('v1.30.2');
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new KubeadmControlPlaneManifest(
            metadata: new ManifestMetadata(name: 'my-cp'),
            spec: new KubeadmControlPlaneSpec(
                replicas: 1,
                version: 'v1.30.2',
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'x'),
            ),
        ))->toThrow(InvalidArgumentException::class);
    });

test('spec serializes kubeadm config with defaults',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new KubeadmControlPlaneSpec(
            replicas: 1,
            version: 'v1.30.2',
            infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'my-cp'),
        );

        $array = $spec->toArray();

        expect($array['kubeadmConfigSpec']['clusterConfiguration']['controllerManager']['extraArgs'])->toBeArray()
            ->and($array['kubeadmConfigSpec']['initConfiguration']['nodeRegistration']['kubeletExtraArgs'])->toBeArray()
            ->and($array['kubeadmConfigSpec']['joinConfiguration']['nodeRegistration']['kubeletExtraArgs'])->toBeArray();
    });
