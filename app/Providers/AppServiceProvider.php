<?php

declare(strict_types=1);

namespace App\Providers;

use App\Client\HttpAuthClient;
use App\Client\LarakubeClient;
use App\Console\Services\SessionManager;
use App\Contracts\AuthClient;
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
