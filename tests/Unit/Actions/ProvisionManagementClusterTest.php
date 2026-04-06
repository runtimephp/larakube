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
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;

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
            kubernetesVersion: 'v1.32.3',
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
            kubernetesVersion: 'v1.32.3',
            force: false,
        ));

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            kubernetesVersion: 'v1.32.3',
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
            kubernetesVersion: 'v1.32.3',
            force: false,
        ));

        $this->kubeconfig->setKubeconfig('kuven-mgmt-local', 'apiVersion: v1\nclusters: [updated]');

        $second = $this->action->handle(new ProvisionManagementClusterData(
            provider: 'docker',
            region: 'local',
            kubernetesVersion: 'v1.32.3',
            force: true,
        ));

        expect($second->id)->not->toBe($first->id)
            ->and($second->status)->toBe('ready');
    });

test('provisions hetzner management cluster with capi pivot',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'secret/hetzner created'),
            'kubectl patch secret*' => Process::result(output: 'secret/hetzner patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: cluster.x-k8s.io/v1beta1'),
            'kubectl apply*' => Process::result(output: 'resources created'),
            '*clusterctl*get kubeconfig*' => Process::result(output: 'apiVersion: v1\nclusters: [hetzner]'),
            '*clusterctl*move*' => Process::result(output: 'Moving objects'),
        ]);

        $result = $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        ));

        expect($result->name)->toBe('kuven-mgmt-fsn1')
            ->and($result->status)->toBe('ready');

        Process::assertRan(fn (PendingProcess $process): bool => str_contains($process->command, 'kubectl create secret generic hetzner'));

        Process::assertRan(fn (PendingProcess $process): bool => str_contains($process->command, 'generate cluster kuven-mgmt-fsn1'));

        Process::assertRan(fn (PendingProcess $process): bool => str_contains($process->command, 'clusterctl') && str_contains($process->command, 'move'));
    });

test('destroys temp kind cluster after hetzner pivot',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'secret created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: cluster.x-k8s.io/v1beta1'),
            'kubectl apply*' => Process::result(output: 'created'),
            '*clusterctl*get kubeconfig*' => Process::result(output: 'apiVersion: v1'),
            '*clusterctl*move*' => Process::result(output: 'done'),
        ]);

        $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        ));

        expect($this->bootstrap->exists('kuven-mgmt-fsn1'))->toBeFalse();
    });

test('creates ssh key when not found in hetzner',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        $keyBase = sys_get_temp_dir().'/kuven-mgmt-fsn1-ssh-key';
        file_put_contents("{$keyBase}.pub", 'ssh-ed25519 AAAA fake-key');
        file_put_contents($keyBase, 'fake-private-key');

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[]}'),
            'ssh-keygen*' => Process::result(output: ''),
            'curl*POST*ssh_keys*' => Process::result(output: '{"ssh_key":{"id":1}}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: cluster.x-k8s.io/v1beta1'),
            'kubectl apply*' => Process::result(output: 'created'),
            '*clusterctl*get kubeconfig*' => Process::result(output: 'apiVersion: v1'),
            '*clusterctl*move*' => Process::result(output: 'done'),
        ]);

        $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        ));

        Process::assertRan(fn (PendingProcess $process): bool => str_contains($process->command, 'ssh-keygen'));
    });

test('throws when capi controllers fail to become ready',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait deployment*' => Process::result(exitCode: 1, errorOutput: 'timeout'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'did not become ready');
    });

test('throws when hcloud secret creation fails',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(exitCode: 1, errorOutput: 'already exists'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Failed to create hcloud secret');
    });

test('throws when ssh key creation fails in hetzner',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        $keyBase = sys_get_temp_dir().'/kuven-mgmt-fsn1-ssh-key';
        file_put_contents("{$keyBase}.pub", 'ssh-ed25519 AAAA fake');
        file_put_contents($keyBase, 'fake-key');

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[]}'),
            'ssh-keygen*' => Process::result(output: ''),
            'curl*POST*ssh_keys*' => Process::result(exitCode: 1, errorOutput: 'unauthorized'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Failed to create SSH key');
    });

test('throws when cluster manifest generation fails',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(exitCode: 1, errorOutput: 'template not found'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Failed to generate cluster manifests');
    });

test('throws when cluster apply fails',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: cluster.x-k8s.io/v1beta1'),
            'kubectl apply*' => Process::result(exitCode: 1, errorOutput: 'validation error'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Failed to apply cluster manifests');
    });

test('throws when waiting for cluster ready times out',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait deployment*' => Process::result(output: 'condition met'),
            'kubectl wait pod*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: v1beta1'),
            'kubectl apply*' => Process::result(output: 'created'),
            'kubectl wait cluster*' => Process::result(exitCode: 1, errorOutput: 'timed out'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Timed out waiting for cluster');
    });

test('throws when getting workload kubeconfig fails',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait deployment*' => Process::result(output: 'condition met'),
            'kubectl wait pod*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: v1beta1'),
            'kubectl apply*' => Process::result(output: 'created'),
            'kubectl wait cluster*' => Process::result(output: 'condition met'),
            '*clusterctl*get kubeconfig*' => Process::result(exitCode: 1, errorOutput: 'not found'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Failed to get kubeconfig');
    });

test('throws when clusterctl move fails',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        Process::fake([
            'kubectl wait deployment*' => Process::result(output: 'condition met'),
            'kubectl wait pod*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: v1beta1'),
            'kubectl apply*' => Process::result(output: 'created'),
            'kubectl wait cluster*' => Process::result(output: 'condition met'),
            '*clusterctl*get kubeconfig*' => Process::result(output: 'apiVersion: v1'),
            '*clusterctl*move*' => Process::result(exitCode: 1, errorOutput: 'connection refused'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Failed to pivot CAPI state');
    });

test('throws when clusterctl download fails',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        $clusterctlPath = sys_get_temp_dir().'/clusterctl-v1.8.10';
        @unlink($clusterctlPath);

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(exitCode: 1, errorOutput: 'network error'),
        ]);

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'Failed to download clusterctl');
    });

test('downloads clusterctl binary when not cached',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', 'test-hcloud-token');

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        $clusterctlPath = sys_get_temp_dir().'/clusterctl-v1.8.10';
        @unlink($clusterctlPath);

        Process::fake([
            'kubectl wait*' => Process::result(output: 'condition met'),
            'kubectl create secret*' => Process::result(output: 'created'),
            'kubectl patch secret*' => Process::result(output: 'patched'),
            'curl*ssh_keys?name=*' => Process::result(output: '{"ssh_keys":[{"name":"kuven-mgmt-fsn1"}]}'),
            'curl*clusterctl*' => Process::result(output: ''),
            '*clusterctl*generate*' => Process::result(output: 'apiVersion: v1beta1'),
            'kubectl apply*' => Process::result(output: 'created'),
            'kubectl wait cluster*' => Process::result(output: 'condition met'),
            '*clusterctl*get kubeconfig*' => Process::result(output: 'apiVersion: v1'),
            '*clusterctl*move*' => Process::result(output: 'done'),
        ]);

        $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        ));

        Process::assertRan(fn (PendingProcess $process): bool => str_contains($process->command, 'curl') && str_contains($process->command, 'clusterctl-darwin-arm64'));

        // Restore for other tests
        file_put_contents($clusterctlPath, 'fake');
    });

test('throws when hcloud token is missing for hetzner provider',
    /**
     * @throws Throwable
     */
    function (): void {
        config()->set('services.hetzner.token', null);

        $this->kubeconfig->setKubeconfig('kuven-mgmt-fsn1', 'apiVersion: v1\nclusters: []');

        expect(fn () => $this->action->handle(new ProvisionManagementClusterData(
            provider: 'hetzner',
            region: 'fsn1',
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'HCLOUD_TOKEN');
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
            kubernetesVersion: 'v1.32.3',
            force: false,
        )))->toThrow(RuntimeException::class, 'clusterctl');
    });
