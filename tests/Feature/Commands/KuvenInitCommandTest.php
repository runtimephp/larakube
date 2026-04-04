<?php

declare(strict_types=1);

use App\Contracts\BootstrapClusterService;
use App\Contracts\CapiInstallerService;
use App\Contracts\KubeconfigReaderService;
use App\Contracts\PrerequisiteChecker;
use App\Enums\ManagementClusterStatus;
use App\Models\ManagementCluster;
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

    $this->app->instance(PrerequisiteChecker::class, $this->prereqs);
    $this->app->instance(BootstrapClusterService::class, $this->bootstrap);
    $this->app->instance(CapiInstallerService::class, $this->capi);
    $this->app->instance(KubeconfigReaderService::class, $this->kubeconfig);
});

test('bootstraps a local management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('kuven:init', ['--provider' => 'docker'])
            ->assertSuccessful();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::query()->sole();

        expect($cluster->provider)->toBe('docker')
            ->and($cluster->region)->toBe('local')
            ->and($cluster->status)->toBe(ManagementClusterStatus::Ready)
            ->and($cluster->kubeconfig)->not->toBeNull();
    });

test('aborts when management cluster already exists',
    /**
     * @throws Throwable
     */
    function (): void {
        ManagementCluster::factory()->ready()->create([
            'provider' => 'docker',
            'region' => 'local',
        ]);

        $this->artisan('kuven:init', ['--provider' => 'docker'])
            ->expectsOutputToContain('already exists')
            ->assertFailed();
    });

test('re-bootstraps with force flag',
    /**
     * @throws Throwable
     */
    function (): void {
        $existing = ManagementCluster::factory()->ready()->create([
            'name' => 'kuven-mgmt-local',
            'provider' => 'docker',
            'region' => 'local',
        ]);

        $this->bootstrap->addCluster($existing->name);

        $this->artisan('kuven:init', ['--provider' => 'docker', '--force' => true])
            ->assertSuccessful();

        expect(ManagementCluster::query()->count())->toBe(1);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::query()->sole();

        expect($cluster->status)->toBe(ManagementClusterStatus::Ready);
    });

test('aborts when prerequisites are missing',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->prereqs->setAvailable(['kind', 'kubectl', 'docker']);

        $this->artisan('kuven:init', ['--provider' => 'docker'])
            ->expectsOutputToContain('clusterctl')
            ->assertFailed();

        expect(ManagementCluster::query()->count())->toBe(0);
    });

test('uses custom region when provided',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->kubeconfig->setKubeconfig('kuven-mgmt-nuremberg', 'apiVersion: v1\nclusters: []');

        $this->artisan('kuven:init', ['--provider' => 'docker', '--region' => 'nuremberg'])
            ->assertSuccessful();

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::query()->sole();

        expect($cluster->region)->toBe('nuremberg');
    });

test('aborts when provider option is missing',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('kuven:init')
            ->expectsOutputToContain('--provider option is required')
            ->assertFailed();
    });
