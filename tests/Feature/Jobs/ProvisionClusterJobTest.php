<?php

declare(strict_types=1);

use App\Actions\MarkClusterFailed;
use App\Actions\ProvisionCluster;
use App\Contracts\ClusterManifestGenerator;
use App\Contracts\ManifestService;
use App\Data\CreateClusterManifestData;
use App\Enums\InfrastructureStatus;
use App\Jobs\ProvisionClusterJob;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;
use App\Services\DockerClusterManifestGenerator;
use App\Services\InMemory\InMemoryManifestService;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->app->instance(ClusterManifestGenerator::class, new DockerClusterManifestGenerator);
});

test('applies manifests and updates cluster status to provisioning',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var InMemoryManifestService $manifestService */
        $manifestService = app(ManifestService::class);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => 'my-cluster',
            'status' => InfrastructureStatus::Provisioning,
        ]);

        $data = new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: "kuven-org-{$infrastructure->organization_id}",
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 2,
        );

        $job = new ProvisionClusterJob($cluster, $data);
        $job->handle(app(ProvisionCluster::class));

        expect($manifestService->applied())->toHaveCount(7);

        $cluster->refresh();

        expect($cluster->status)->toBe(InfrastructureStatus::Provisioning);
    });

test('marks cluster as failed on final failure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => 'my-cluster',
            'status' => InfrastructureStatus::Provisioning,
        ]);

        $data = new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: "kuven-org-{$infrastructure->organization_id}",
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        );

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn (string $message): bool => str_contains($message, 'failed'));

        $job = new ProvisionClusterJob($cluster, $data);
        $job->failed(new RuntimeException('K8s API unreachable'));

        $cluster->refresh();

        expect($cluster->status)->toBe(InfrastructureStatus::Failed);
    });

test('logs error when marking cluster as failed throws',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => 'my-cluster',
            'status' => InfrastructureStatus::Provisioning,
        ]);

        $data = new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: "kuven-org-{$infrastructure->organization_id}",
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        );

        $this->app->bind(MarkClusterFailed::class, function (): never {
            throw new RuntimeException('DB connection lost');
        });

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn (string $message): bool => str_contains($message, 'Failed to mark cluster as failed'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn (string $message): bool => str_contains($message, 'Cluster provisioning failed'));

        $job = new ProvisionClusterJob($cluster, $data);
        $job->failed(new RuntimeException('K8s API unreachable'));
    });

test('job is dispatched to kubernetes queue with retries',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $data = new CreateClusterManifestData(
            name: 'my-cluster',
            namespace: 'kuven-org-123',
            provider: 'docker',
            kubernetesVersion: 'v1.30.2',
            controlPlaneCount: 1,
            workerCount: 1,
        );

        $job = new ProvisionClusterJob($cluster, $data);

        expect($job->queue)->toBe('kubernetes')
            ->and($job->tries)->toBe(3)
            ->and($job->backoff)->toBe(30);
    });
