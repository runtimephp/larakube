<?php

declare(strict_types=1);

namespace App\Providers;

use App\Client\HttpAuthClient;
use App\Client\HttpCloudProviderClient;
use App\Client\HttpInfrastructureClient;
use App\Client\HttpOrganizationClient;
use App\Client\HttpServerClient;
use App\Client\LarakubeClient;
use App\Console\Services\SessionManager;
use App\Contracts\AuthClient;
use App\Contracts\BootstrapClusterService;
use App\Contracts\CapiInstallerService;
use App\Contracts\CloudProviderClient;
use App\Contracts\InfrastructureClient;
use App\Contracts\KubeconfigReaderService;
use App\Contracts\OrganizationClient;
use App\Contracts\PrerequisiteChecker;
use App\Contracts\ServerClient;
use App\Services\ClusterctlCapiInstallerService;
use App\Services\KindBootstrapClusterService;
use App\Services\KindKubeconfigReaderService;
use App\Services\ProcessPrerequisiteChecker;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as TelescopeBaseServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(TelescopeBaseServiceProvider::class)) { // @codeCoverageIgnore
            $this->app->register(TelescopeBaseServiceProvider::class); // @codeCoverageIgnore
            $this->app->register(TelescopeServiceProvider::class); // @codeCoverageIgnore
        }

        $this->app->singleton(SessionManager::class);

        $this->app->singleton(LarakubeClient::class, function (Application $app): LarakubeClient {
            $session = $app->make(SessionManager::class);
            $user = $session->getUser();
            $organization = $session->getOrganization();
            $infrastructure = $session->getInfrastructure();

            return new LarakubeClient(
                baseUrl: config('larakube.api_url'),
                token: $user?->token,
                organizationId: $organization?->id,
                infrastructureId: $infrastructure?->id,
            );
        });

        $this->app->bind(AuthClient::class, HttpAuthClient::class);
        $this->app->bind(OrganizationClient::class, HttpOrganizationClient::class);
        $this->app->bind(CloudProviderClient::class, HttpCloudProviderClient::class);
        $this->app->bind(InfrastructureClient::class, HttpInfrastructureClient::class);
        $this->app->bind(ServerClient::class, HttpServerClient::class);

        $this->app->bind(PrerequisiteChecker::class, ProcessPrerequisiteChecker::class);
        $this->app->bind(BootstrapClusterService::class, KindBootstrapClusterService::class);
        $this->app->bind(CapiInstallerService::class, ClusterctlCapiInstallerService::class);
        $this->app->bind(KubeconfigReaderService::class, KindKubeconfigReaderService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();
    }

    private function configureModels(): void
    {
        Model::unguard();
    }
}
