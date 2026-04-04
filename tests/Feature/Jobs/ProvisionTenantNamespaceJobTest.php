<?php

declare(strict_types=1);

use App\Actions\ProvisionTenantNamespace;
use App\Contracts\NamespaceService;
use App\Contracts\NetworkPolicyService;
use App\Contracts\ResourceQuotaService;
use App\Contracts\RoleBindingService;
use App\Contracts\RoleService;
use App\Contracts\ServiceAccountService;
use App\Http\Integrations\Kubernetes\Data\NamespaceData;
use App\Jobs\ProvisionTenantNamespaceJob;
use App\Models\Organization;
use App\Services\InMemory\InMemoryNamespaceService;
use App\Services\InMemory\InMemoryNetworkPolicyService;
use App\Services\InMemory\InMemoryResourceQuotaService;
use App\Services\InMemory\InMemoryRoleBindingService;
use App\Services\InMemory\InMemoryRoleService;
use App\Services\InMemory\InMemoryServiceAccountService;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->namespaceService = new InMemoryNamespaceService;
    $this->serviceAccountService = new InMemoryServiceAccountService;
    $this->roleService = new InMemoryRoleService;
    $this->roleBindingService = new InMemoryRoleBindingService;
    $this->networkPolicyService = new InMemoryNetworkPolicyService;
    $this->resourceQuotaService = new InMemoryResourceQuotaService;

    $this->app->instance(NamespaceService::class, $this->namespaceService);
    $this->app->instance(ServiceAccountService::class, $this->serviceAccountService);
    $this->app->instance(RoleService::class, $this->roleService);
    $this->app->instance(RoleBindingService::class, $this->roleBindingService);
    $this->app->instance(NetworkPolicyService::class, $this->networkPolicyService);
    $this->app->instance(ResourceQuotaService::class, $this->resourceQuotaService);
});

test('provisions tenant namespace for organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $job = new ProvisionTenantNamespaceJob($organization);
        $job->handle(app(ProvisionTenantNamespace::class));

        expect($this->namespaceService->namespaces())->toHaveCount(1)
            ->and($this->namespaceService->namespaces()[0])->toBe("kuven-org-{$organization->id}");
    });

test('throws on failure so laravel can retry',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $this->app->instance(NamespaceService::class, new class implements NamespaceService
        {
            public function create(string $name): NamespaceData
            {
                throw new RuntimeException('Management cluster unreachable');
            }
        });

        $job = new ProvisionTenantNamespaceJob($organization);

        expect(fn () => $job->handle(app(ProvisionTenantNamespace::class)))
            ->toThrow(RuntimeException::class, 'Management cluster unreachable');
    });

test('logs error on final failure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn (string $message): bool => str_contains($message, 'failed'));

        $job = new ProvisionTenantNamespaceJob($organization);
        $job->failed(new RuntimeException('Management cluster unreachable'));
    });

test('retries up to 3 times with 30 second backoff',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $job = new ProvisionTenantNamespaceJob($organization);

        expect($job->tries)->toBe(3)
            ->and($job->backoff)->toBe(30);
    });

test('job is dispatched to kubernetes queue',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $job = new ProvisionTenantNamespaceJob($organization);

        expect($job->queue)->toBe('kubernetes');
    });
