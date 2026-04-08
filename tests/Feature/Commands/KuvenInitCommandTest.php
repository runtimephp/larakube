<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryManagementClusterClient;
use App\Console\Services\SessionManager;
use App\Contracts\BootstrapClusterService;
use App\Contracts\CapiInstallerService;
use App\Contracts\KubeconfigReaderService;
use App\Contracts\ManagementClusterClient;
use App\Contracts\PrerequisiteChecker;
use App\Data\CreateManagementClusterData;
use App\Models\User;
use App\Services\InMemory\InMemoryBootstrapClusterService;
use App\Services\InMemory\InMemoryCapiInstallerService;
use App\Services\InMemory\InMemoryKubeconfigReaderService;
use App\Services\InMemory\InMemoryPrerequisiteChecker;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);

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

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'operator@kuven.io',
        'password' => 'password',
    ]);

    $userData = app(LoginUser::class)->handle('operator@kuven.io', 'password');
    app(SessionManager::class)->setUser($userData);
});

test('bootstraps a local management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('kuven:init', ['--provider' => 'docker'])
            ->expectsOutputToContain('is ready')
            ->assertSuccessful();

        $cluster = $this->clusterClient->findByProviderAndRegion('docker', 'local');

        expect($cluster)->not->toBeNull()
            ->and($cluster->status)->toBe('ready');
    });

test('aborts when management cluster already exists',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->clusterClient->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            providerId: 'docker',
            platformRegionId: 'local',
            version: 'v1.32.3',
        ));

        $this->artisan('kuven:init', ['--provider' => 'docker'])
            ->expectsOutputToContain('already exists')
            ->assertFailed();
    });

test('re-bootstraps with force flag',
    /**
     * @throws Throwable
     */
    function (): void {
        $existing = $this->clusterClient->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            providerId: 'docker',
            platformRegionId: 'local',
            version: 'v1.32.3',
        ));

        $this->bootstrap->addCluster($existing->name);

        $this->artisan('kuven:init', ['--provider' => 'docker', '--force' => true])
            ->expectsOutputToContain('is ready')
            ->assertSuccessful();
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
    });

test('uses custom region when provided',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->kubeconfig->setKubeconfig('kuven-mgmt-nuremberg', 'apiVersion: v1\nclusters: []');

        $this->artisan('kuven:init', ['--provider' => 'docker', '--region' => 'nuremberg'])
            ->assertSuccessful();

        $cluster = $this->clusterClient->findByProviderAndRegion('docker', 'nuremberg');

        expect($cluster)->not->toBeNull()
            ->and($cluster->platformRegionId)->toBe('nuremberg');
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

test('requires authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        app(SessionManager::class)->clear();

        $this->artisan('kuven:init', ['--provider' => 'docker'])
            ->expectsOutputToContain('not logged in')
            ->assertFailed();
    });
