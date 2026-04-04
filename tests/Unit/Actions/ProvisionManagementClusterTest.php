<?php

declare(strict_types=1);

use App\Actions\ProvisionManagementCluster;
use App\Client\InMemoryManagementClusterClient;
use App\Contracts\BootstrapClusterService;
use App\Contracts\CapiInstallerService;
use App\Contracts\KubeconfigReaderService;
use App\Contracts\ManagementClusterClient;
use App\Contracts\PrerequisiteChecker;
use App\Data\ProvisionManagementClusterData;
use App\Services\InMemory\InMemoryBootstrapClusterService;
use App\Services\InMemory\InMemoryCapiInstallerService;
use App\Services\InMemory\InMemoryKubeconfigReaderService;
use App\Services\InMemory\InMemoryPrerequisiteChecker;

beforeEach(function (): void {
    $this->prereqs = new InMemoryPrerequisiteChecker;
    $this->prereqs->setAvailable(['kind', 'clusterctl', 'kubectl', 'docker']);

    $this->bootstrap = new InMemoryBootstrapClusterService;
    $this->capi = new InMemoryCapiInstallerService;

    $this->kubeconfig = new InMemoryKubeconfigReaderService;
    $this->kubeconfig->setKubeconfig('kuven-mgmt-local', 'apiVersion: v1\nclusters: []');

    $this->clusterClient = new InMemoryManagementClusterClient;

    $this->app->instance(PrerequisiteChecker::class, $this->prereqs);
    $this->app->instance(BootstrapClusterService::class, $this->bootstrap);
    $this->app->instance(CapiInstallerService::class, $this->capi);
    $this->app->instance(KubeconfigReaderService::class, $this->kubeconfig);
    $this->app->instance(ManagementClusterClient::class, $this->clusterClient);

    $this->action = $this->app->make(ProvisionManagementCluster::class);
});

test('provisions a management cluster end to end',
    /**
     * @throws Throwable
     */
    function (): void {
        $result = $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            force: false,
        ));

        expect($result->name)->toBe('kuven-mgmt-local')
            ->and($result->status)->toBe('ready')
            ->and($this->bootstrap->exists('kuven-mgmt-local'))->toBeTrue()
            ->and($this->capi->installations())->toHaveCount(1)
            ->and($this->clusterClient->getKubeconfig($result->id))->not->toBeNull();
    });

test('throws when cluster already exists and force is false',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            force: false,
        ));

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            force: false,
        )))->toThrow(RuntimeException::class, 'already exists');
    });

test('re-provisions with force flag',
    /**
     * @throws Throwable
     */
    function (): void {
        $first = $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            force: false,
        ));

        $this->kubeconfig->setKubeconfig('kuven-mgmt-local', 'apiVersion: v1\nclusters: [updated]');

        $second = $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            force: true,
        ));

        expect($second->id)->not->toBe($first->id)
            ->and($second->status)->toBe('ready');
    });

test('throws when prerequisites are missing',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->prereqs->setAvailable(['kind', 'kubectl', 'docker']);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            force: false,
        )))->toThrow(RuntimeException::class, 'clusterctl');
    });
