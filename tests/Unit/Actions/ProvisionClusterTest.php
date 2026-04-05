<?php

declare(strict_types=1);

use App\Actions\ProvisionCluster;
use App\Contracts\ClusterManifestGenerator;
use App\Contracts\ManifestService;
use App\Data\CreateClusterManifestData;
use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Services\DockerClusterManifestGenerator;
use App\Services\InMemory\InMemoryManifestService;

beforeEach(function (): void {
    $this->manifestService = new InMemoryManifestService;

    $this->app->instance(ManifestService::class, $this->manifestService);
    $this->app->instance(ClusterManifestGenerator::class, new DockerClusterManifestGenerator);

    $this->action = $this->app->make(ProvisionCluster::class);
});

test('applies all generated manifests',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 2,
        );

        $this->action->handle($data);

        expect($this->manifestService->applied())->toHaveCount(7);
    });

test('applies manifests in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        );

        $this->action->handle($data);

        $kinds = array_map(
            fn (ManifestContract $m): Kind => $m->kind(),
            $this->manifestService->applied(),
        );

        expect($kinds[0])->toBe(Kind::Cluster)
            ->and($kinds[1])->toBe(Kind::DockerCluster)
            ->and($kinds[2])->toBe(Kind::KubeadmControlPlane)
            ->and($kinds[3])->toBe(Kind::DockerMachineTemplate)
            ->and($kinds[4])->toBe(Kind::MachineDeployment)
            ->and($kinds[5])->toBe(Kind::DockerMachineTemplate)
            ->and($kinds[6])->toBe(Kind::KubeadmConfigTemplate);
    });

test('all manifests target the correct namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-456',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        );

        $this->action->handle($data);

        foreach ($this->manifestService->applied() as $manifest) {
            expect($manifest->namespace())->toBe('kuven-org-456');
        }
    });
