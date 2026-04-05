<?php

namespace Tests;

use App\Contracts\ManifestService;
use App\Contracts\NamespaceService;
use App\Contracts\NetworkPolicyService;
use App\Contracts\ResourceQuotaService;
use App\Contracts\RoleBindingService;
use App\Contracts\RoleService;
use App\Contracts\ServiceAccountService;
use App\Services\InMemory\InMemoryManifestService;
use App\Services\InMemory\InMemoryNamespaceService;
use App\Services\InMemory\InMemoryNetworkPolicyService;
use App\Services\InMemory\InMemoryResourceQuotaService;
use App\Services\InMemory\InMemoryRoleBindingService;
use App\Services\InMemory\InMemoryRoleService;
use App\Services\InMemory\InMemoryServiceAccountService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $sessionsPath = sys_get_temp_dir().'/larakube-sessions-'.getmypid().'-'.uniqid();
        config()->set('larakube.sessions_path', $sessionsPath);

        $this->app->instance(ManifestService::class, new InMemoryManifestService);
        $this->app->instance(NamespaceService::class, new InMemoryNamespaceService);
        $this->app->instance(ServiceAccountService::class, new InMemoryServiceAccountService);
        $this->app->instance(RoleService::class, new InMemoryRoleService);
        $this->app->instance(RoleBindingService::class, new InMemoryRoleBindingService);
        $this->app->instance(NetworkPolicyService::class, new InMemoryNetworkPolicyService);
        $this->app->instance(ResourceQuotaService::class, new InMemoryResourceQuotaService);
    }

    protected function tearDown(): void
    {
        $sessionsPath = config('larakube.sessions_path');

        if (is_dir($sessionsPath)) {
            $files = glob($sessionsPath.'/*.json');
            if (is_array($files)) {
                array_map(unlink(...), $files);
            }
            rmdir($sessionsPath);
        }

        parent::tearDown();
    }
}
