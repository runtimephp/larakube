<?php

declare(strict_types=1);

use App\Data\CreateClusterManifestData;
use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentManifest;
use App\Http\Integrations\Kubernetes\Manifests\SecretManifest;
use App\Services\HetznerClusterManifestGenerator;

beforeEach(function (): void {
    $this->generator = new HetznerClusterManifestGenerator;
});

test('generates all required capi manifests for hetzner provider',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'prod-cluster',
            namespace: 'kuven-org-123',
            provider: 'hetzner',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 3,
            workerCount: 5,
            region: 'nuremberg',
            machineType: 'cx31',
        ));

        expect($manifests)->toHaveCount(8)
            ->and($manifests[0])->toBeInstanceOf(SecretManifest::class)
            ->and($manifests[1])->toBeInstanceOf(ClusterManifest::class)
            ->and($manifests[2])->toBeInstanceOf(HetznerClusterManifest::class)
            ->and($manifests[3])->toBeInstanceOf(KubeadmControlPlaneManifest::class)
            ->and($manifests[4])->toBeInstanceOf(HetznerMachineTemplateManifest::class)
            ->and($manifests[5])->toBeInstanceOf(MachineDeploymentManifest::class)
            ->and($manifests[6])->toBeInstanceOf(HetznerMachineTemplateManifest::class)
            ->and($manifests[7])->toBeInstanceOf(KubeadmConfigTemplateManifest::class);
    });

test('all manifests implement ManifestContract',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'prod-cluster',
            namespace: 'kuven-org-123',
            provider: 'hetzner',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
            region: 'nuremberg',
            machineType: 'cx31',
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
            name: 'prod-cluster',
            namespace: 'kuven-org-456',
            provider: 'hetzner',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
            region: 'nuremberg',
            machineType: 'cx31',
        ));

        foreach ($manifests as $manifest) {
            expect($manifest->namespace())->toBe('kuven-org-456');
        }
    });

test('cluster references hetzner infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'prod-cluster',
            namespace: 'kuven-org-123',
            provider: 'hetzner',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
            region: 'nuremberg',
            machineType: 'cx31',
        ));

        /** @var ClusterManifest $cluster */
        $cluster = $manifests[1];

        expect($cluster->spec->infrastructureRef->kind)->toBe(Kind::HetznerCluster)
            ->and($cluster->spec->infrastructureRef->apiVersion)->toBe(ApiVersion::CapiInfrastructureV1Beta1);
    });

test('hetzner cluster manifest includes region',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'prod-cluster',
            namespace: 'kuven-org-123',
            provider: 'hetzner',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
            region: 'nuremberg',
            machineType: 'cx31',
        ));

        /** @var HetznerClusterManifest $hetznerCluster */
        $hetznerCluster = $manifests[2];

        expect($hetznerCluster->toArray()['spec']['controlPlaneRegion'])->toBe('nuremberg');
    });

test('hetzner machine template includes server type',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifests = $this->generator->generate(new CreateClusterManifestData(
            name: 'prod-cluster',
            namespace: 'kuven-org-123',
            provider: 'hetzner',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
            region: 'nuremberg',
            machineType: 'cx31',
        ));

        /** @var HetznerMachineTemplateManifest $machineTemplate */
        $machineTemplate = $manifests[4];

        expect($machineTemplate->toArray()['spec']['template']['spec']['serverType'])->toBe('cx31');
    });
