<?php

declare(strict_types=1);

use App\Data\CreateClusterManifestData;
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

        $kinds = array_column($manifests, 'kind');

        expect($manifests)->toHaveCount(7)
            ->and($kinds)->toContain('Cluster')
            ->and($kinds)->toContain('DockerCluster')
            ->and($kinds)->toContain('KubeadmControlPlane')
            ->and($kinds)->toContain('DockerMachineTemplate')
            ->and($kinds)->toContain('MachineDeployment')
            ->and($kinds)->toContain('KubeadmConfigTemplate');
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
            expect($manifest['metadata']['namespace'])->toBe('kuven-org-456');
        }
    });

test('sets correct kubernetes version',
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

        $controlPlane = collect($manifests)->firstWhere('kind', 'KubeadmControlPlane');

        expect($controlPlane['spec']['version'])->toBe('v1.30.2');
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

        $controlPlane = collect($manifests)->firstWhere('kind', 'KubeadmControlPlane');

        expect($controlPlane['spec']['replicas'])->toBe(3);
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

        $machineDeployment = collect($manifests)->firstWhere('kind', 'MachineDeployment');

        expect($machineDeployment['spec']['replicas'])->toBe(5);
    });
