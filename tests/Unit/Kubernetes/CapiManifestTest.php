<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\CidrBlock;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterNetworkSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerMachineTemplateSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

// --- ObjectReference ---

test('object reference serializes to array',
    /**
     * @throws Throwable
     */
    function (): void {
        $ref = new ObjectReference(ApiVersion::CapiCoreV1Beta1, Kind::Cluster, 'my-cluster');

        expect($ref->toArray())->toBe([
            'apiVersion' => 'cluster.x-k8s.io/v1beta1',
            'kind' => 'Cluster',
            'name' => 'my-cluster',
        ]);
    });

// --- CidrBlock ---

test('cidr block converts to string',
    /**
     * @throws Throwable
     */
    function (): void {
        $cidr = new CidrBlock('10.0.0.0/8');

        expect($cidr->toString())->toBe('10.0.0.0/8');
    });

// --- ClusterNetworkSpec ---

test('cluster network spec serializes with defaults',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new ClusterNetworkSpec;

        expect($spec->toArray())->toBe([
            'pods' => ['cidrBlocks' => ['192.168.0.0/16']],
            'services' => ['cidrBlocks' => ['10.128.0.0/12']],
        ]);
    });

test('cluster network spec serializes with custom cidrs',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new ClusterNetworkSpec(
            podCidrBlocks: [new CidrBlock('10.244.0.0/16')],
            serviceCidrBlocks: [new CidrBlock('10.96.0.0/12')],
        );

        expect($spec->toArray())->toBe([
            'pods' => ['cidrBlocks' => ['10.244.0.0/16']],
            'services' => ['cidrBlocks' => ['10.96.0.0/12']],
        ]);
    });

// --- ClusterManifest ---

test('cluster manifest serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new ClusterManifest(
            metadata: new ManifestMetadata(name: 'my-cluster', namespace: 'kuven-org-123'),
            spec: new ClusterSpec(
                controlPlaneRef: new ObjectReference(ApiVersion::CapiControlPlaneV1Beta1, Kind::KubeadmControlPlane, 'my-cluster-cp'),
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

test('cluster manifest rejects missing namespace',
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

// --- KubeadmControlPlaneManifest ---

test('kubeadm control plane manifest serializes and exposes routing metadata',
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

test('kubeadm control plane manifest rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new KubeadmControlPlaneManifest(
            metadata: new ManifestMetadata(name: 'my-cp'),
            spec: new KubeadmControlPlaneSpec(
                replicas: 1,
                version: 'v1.30.2',
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'my-cp'),
            ),
        ))->toThrow(InvalidArgumentException::class);
    });

// --- MachineDeploymentManifest ---

test('machine deployment manifest serializes and exposes routing metadata',
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

test('machine deployment manifest rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new MachineDeploymentManifest(
            metadata: new ManifestMetadata(name: 'my-md-0'),
            spec: new MachineDeploymentSpec(
                clusterName: 'c',
                replicas: 1,
                version: 'v1.30.2',
                bootstrapConfigRef: new ObjectReference(ApiVersion::CapiBootstrapV1Beta1, Kind::KubeadmConfigTemplate, 'x'),
                infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, 'x'),
            ),
        ))->toThrow(InvalidArgumentException::class);
    });

// --- KubeadmConfigTemplateManifest ---

test('kubeadm config template manifest serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new KubeadmConfigTemplateManifest(
            metadata: new ManifestMetadata(name: 'my-md-0', namespace: 'kuven-org-123'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiBootstrapV1Beta1)
            ->and($manifest->kind())->toBe(Kind::KubeadmConfigTemplate)
            ->and($manifest->resource())->toBe('kubeadmconfigtemplates')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec'])->toBeArray();
    });

test('kubeadm config template manifest rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new KubeadmConfigTemplateManifest(
            metadata: new ManifestMetadata(name: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });

// --- DockerClusterManifest ---

test('docker cluster manifest serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new DockerClusterManifest(
            metadata: new ManifestMetadata(name: 'my-cluster', namespace: 'kuven-org-123'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::DockerCluster)
            ->and($manifest->resource())->toBe('dockerclusters')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['apiVersion'])->toBe('infrastructure.cluster.x-k8s.io/v1beta1')
            ->and($manifest->toArray()['kind'])->toBe('DockerCluster')
            ->and($manifest->toArray()['metadata']['name'])->toBe('my-cluster');
    });

test('docker cluster manifest rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new DockerClusterManifest(
            metadata: new ManifestMetadata(name: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });

// --- DockerMachineTemplateManifest ---

test('docker machine template manifest serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new DockerMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'my-cp', namespace: 'kuven-org-123'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::DockerMachineTemplate)
            ->and($manifest->resource())->toBe('dockermachinetemplates')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['template']['spec']['extraMounts'])->toBeArray();
    });

test('docker machine template manifest rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new DockerMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });

// --- DockerMachineTemplateSpec ---

test('docker machine template spec serializes with extra mounts',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new DockerMachineTemplateSpec;

        expect($spec->toArray()['template']['spec']['extraMounts'])->toHaveCount(1)
            ->and($spec->toArray()['template']['spec']['extraMounts'][0]['containerPath'])->toBe('/var/run/docker.sock');
    });

// --- KubeadmControlPlaneSpec ---

test('kubeadm control plane spec serializes with defaults',
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

        expect($array['replicas'])->toBe(1)
            ->and($array['version'])->toBe('v1.30.2')
            ->and($array['machineTemplate']['infrastructureRef']['kind'])->toBe('DockerMachineTemplate')
            ->and($array['kubeadmConfigSpec']['clusterConfiguration']['controllerManager']['extraArgs'])->toBeArray()
            ->and($array['kubeadmConfigSpec']['initConfiguration']['nodeRegistration']['kubeletExtraArgs'])->toBeArray()
            ->and($array['kubeadmConfigSpec']['joinConfiguration']['nodeRegistration']['kubeletExtraArgs'])->toBeArray();
    });

// --- KubeadmConfigTemplateSpec ---

test('kubeadm config template spec serializes with defaults',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new KubeadmConfigTemplateSpec;

        $array = $spec->toArray();

        expect($array['template']['spec']['joinConfiguration']['nodeRegistration']['kubeletExtraArgs'])->toBeArray();
    });

// --- MachineDeploymentSpec ---

test('machine deployment spec serializes correctly',
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
            ->and($array['template']['spec']['version'])->toBe('v1.30.2')
            ->and($array['template']['spec']['bootstrap']['configRef']['kind'])->toBe('KubeadmConfigTemplate')
            ->and($array['template']['spec']['infrastructureRef']['kind'])->toBe('DockerMachineTemplate');
    });

// --- ClusterSpec ---

test('cluster spec serializes with references and network',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new ClusterSpec(
            controlPlaneRef: new ObjectReference(ApiVersion::CapiControlPlaneV1Beta1, Kind::KubeadmControlPlane, 'my-cp'),
            infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerCluster, 'my-cluster'),
        );

        $array = $spec->toArray();

        expect($array['controlPlaneRef']['kind'])->toBe('KubeadmControlPlane')
            ->and($array['infrastructureRef']['kind'])->toBe('DockerCluster')
            ->and($array['clusterNetwork']['pods']['cidrBlocks'])->toBe(['192.168.0.0/16']);
    });
