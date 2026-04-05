<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new MachineDeploymentManifest(
            metadata: new ManifestMetadata(name: 'my-md-0', namespace: 'kuven-org-123'),
            spec: new MachineDeploymentSpec(
                clusterName: 'my-cluster',
                replicas: 5,
                version: 'v1.30.2',
                bootstrapConfigRef: new ObjectReference(ApiVersion::CapiBootstrapV1Beta1, Kind::KubeadmConfigTemplate, 'my-md-0'),
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'my-md-0'),
            ),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiCoreV1Beta1)
            ->and($manifest->kind())->toBe(Kind::MachineDeployment)
            ->and($manifest->resource())->toBe('machinedeployments')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['replicas'])->toBe(5);
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new MachineDeploymentManifest(
            metadata: new ManifestMetadata(name: 'x'),
            spec: new MachineDeploymentSpec(
                clusterName: 'c',
                replicas: 1,
                version: 'v1.30.2',
                bootstrapConfigRef: new ObjectReference(ApiVersion::CapiBootstrapV1Beta1, Kind::KubeadmConfigTemplate, 'x'),
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'x'),
            ),
        ))->toThrow(InvalidArgumentException::class);
    });

test('spec serializes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new MachineDeploymentSpec(
            clusterName: 'my-cluster',
            replicas: 3,
            version: 'v1.30.2',
            bootstrapConfigRef: new ObjectReference(ApiVersion::CapiBootstrapV1Beta1, Kind::KubeadmConfigTemplate, 'my-md-0'),
            infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'my-md-0'),
        );

        $array = $spec->toArray();

        expect($array['clusterName'])->toBe('my-cluster')
            ->and($array['replicas'])->toBe(3)
            ->and($array['template']['spec']['bootstrap']['configRef']['kind'])->toBe('KubeadmConfigTemplate')
            ->and($array['template']['spec']['infrastructureRef']['kind'])->toBe('DockerMachineTemplate');
    });
