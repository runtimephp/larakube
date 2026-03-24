<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ServerManagerInterface;
use App\Contracts\ServiceFactoryInterface;
use App\Managers\ServerManager;
use App\Services\Factories\CloudProviderServiceFactory;
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
        $this->app->bind(ServiceFactoryInterface::class, CloudProviderServiceFactory::class);
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
