<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ManifestService;
use App\Contracts\NamespaceService;
use App\Contracts\NetworkPolicyService;
use App\Contracts\ResourceQuotaService;
use App\Contracts\RoleBindingService;
use App\Contracts\RoleService;
use App\Contracts\ServiceAccountService;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Models\ManagementCluster;
use App\Services\KubernetesManifestService;
use App\Services\KubernetesNamespaceService;
use App\Services\KubernetesNetworkPolicyService;
use App\Services\KubernetesResourceQuotaService;
use App\Services\KubernetesRoleBindingService;
use App\Services\KubernetesRoleService;
use App\Services\KubernetesServiceAccountService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class KubernetesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(KubernetesConnector::class, function (Application $app): KubernetesConnector {
            $cluster = ManagementCluster::query()
                ->where('status', 'ready')
                ->first();

            if (! $cluster || ! $cluster->kubeconfig) {
                return new KubernetesConnector(
                    server: '',
                    token: '',
                );
            }

            return new KubernetesConnector(
                server: self::extractServer($cluster->kubeconfig),
                token: self::extractToken($cluster->kubeconfig),
                verifySsl: false,
            );
        });

        $this->app->bind(ManifestService::class, KubernetesManifestService::class);
        $this->app->bind(NamespaceService::class, KubernetesNamespaceService::class);
        $this->app->bind(ServiceAccountService::class, KubernetesServiceAccountService::class);
        $this->app->bind(RoleService::class, KubernetesRoleService::class);
        $this->app->bind(RoleBindingService::class, KubernetesRoleBindingService::class);
        $this->app->bind(NetworkPolicyService::class, KubernetesNetworkPolicyService::class);
        $this->app->bind(ResourceQuotaService::class, KubernetesResourceQuotaService::class);
    }

    private static function extractServer(string $kubeconfig): string
    {
        if (preg_match('/server:\s*(.+)/', $kubeconfig, $matches)) {
            return mb_trim($matches[1]);
        }

        return '';
    }

    private static function extractToken(string $kubeconfig): string
    {
        if (preg_match('/token:\s*(.+)/', $kubeconfig, $matches)) {
            return mb_trim($matches[1]);
        }

        return '';
    }
}
