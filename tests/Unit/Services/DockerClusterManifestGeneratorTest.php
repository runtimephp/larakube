<?php

declare(strict_types=1);

use App\Data\CreateClusterManifestData;
use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentManifest;
use App\Services\DockerClusterManifestGenerator;

beforeEach(function (): void {
    $this->generator = new DockerClusterManifestGenerator;
});

test('generates all required capi manifests for docker provider',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 2,
        ));

        expect($manifests)->toHaveCount(7)
            ->and($manifests[0])->toBeInstanceOf(ClusterManifest::class)
            ->and($manifests[1])->toBeInstanceOf(DockerClusterManifest::class)
            ->and($manifests[2])->toBeInstanceOf(KubeadmControlPlaneManifest::class)
            ->and($manifests[3])->toBeInstanceOf(DockerMachineTemplateManifest::class)
            ->and($manifests[4])->toBeInstanceOf(MachineDeploymentManifest::class)
            ->and($manifests[5])->toBeInstanceOf(DockerMachineTemplateManifest::class)
            ->and($manifests[6])->toBeInstanceOf(KubeadmConfigTemplateManifest::class);
    });

test('all manifests implement ManifestContract',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        ));

        foreach ($manifests as $manifest) {
            expect($manifest)->toBeInstanceOf(ManifestContract::class);
        }
    });

test('sets correct namespace on all manifests',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-456',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        ));

        foreach ($manifests as $manifest) {
            expect($manifest->namespace())->toBe('kuven-org-456');
        }
    });

test('sets correct kubernetes version on control plane',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        ));

        /** @var KubeadmControlPlaneManifest $controlPlane */
        $controlPlane = collect($manifests)->first(fn (ManifestContract $m): bool => $m->kind() === Kind::KubeadmControlPlane);

        expect($controlPlane->spec->version)->toBe('v1.30.2');
    });

test('sets correct control plane replica count',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 3,
            workerCount: 1,
        ));

        /** @var KubeadmControlPlaneManifest $controlPlane */
        $controlPlane = collect($manifests)->first(fn (ManifestContract $m): bool => $m->kind() === Kind::KubeadmControlPlane);

        expect($controlPlane->spec->replicas)->toBe(3);
    });

test('sets correct worker replica count',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 5,
        ));

        /** @var MachineDeploymentManifest $machineDeployment */
        $machineDeployment = collect($manifests)->first(fn (ManifestContract $m): bool => $m->kind() === Kind::MachineDeployment);

        expect($machineDeployment->spec->replicas)->toBe(5);
    });

test('cluster manifest references correct infrastructure and control plane',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        ));

        /** @var ClusterManifest $cluster */
        $cluster = $manifests[0];

        expect($cluster->spec->infrastructureRef->kind)->toBe(Kind::DockerCluster)
            ->and($cluster->spec->infrastructureRef->apiVersion)->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($cluster->spec->controlPlaneRef->kind)->toBe(Kind::KubeadmControlPlane);
    });
