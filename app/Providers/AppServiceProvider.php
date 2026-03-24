<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\CloudProviderClientFactoryInterface;
use App\Contracts\ServerManagerInterface;
use App\Managers\ServerManager;
use App\Services\CloudProviders\CloudProviderClientFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Console\Services\SessionManager::class);
        $this->app->bind(CloudProviderClientFactoryInterface::class, CloudProviderClientFactory::class);
        $this->app->bind(ServerManagerInterface::class, ServerManager::class);
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
