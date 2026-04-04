<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\NamespaceService;
use App\Contracts\NetworkPolicyService;
use App\Contracts\ResourceQuotaService;
use App\Contracts\RoleBindingService;
use App\Contracts\RoleService;
use App\Contracts\ServiceAccountService;
use App\Services\KubernetesNamespaceService;
use App\Services\KubernetesNetworkPolicyService;
use App\Services\KubernetesResourceQuotaService;
use App\Services\KubernetesRoleBindingService;
use App\Services\KubernetesRoleService;
use App\Services\KubernetesServiceAccountService;
use Illuminate\Support\ServiceProvider;

final class KubernetesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NamespaceService::class, KubernetesNamespaceService::class);
        $this->app->bind(ServiceAccountService::class, KubernetesServiceAccountService::class);
        $this->app->bind(RoleService::class, KubernetesRoleService::class);
        $this->app->bind(RoleBindingService::class, KubernetesRoleBindingService::class);
        $this->app->bind(NetworkPolicyService::class, KubernetesNetworkPolicyService::class);
        $this->app->bind(ResourceQuotaService::class, KubernetesResourceQuotaService::class);
    }
}
