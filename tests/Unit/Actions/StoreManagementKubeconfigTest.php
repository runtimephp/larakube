<?php

declare(strict_types=1);

use App\Actions\StoreManagementKubeconfig;
use App\Contracts\KubeconfigReaderService;
use App\Models\ManagementCluster;
use App\Services\InMemory\InMemoryKubeconfigReaderService;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->reader = new InMemoryKubeconfigReaderService;
    $this->app->instance(KubeconfigReaderService::class, $this->reader);
    $this->action = $this->app->make(StoreManagementKubeconfig::class);
});

test('reads kubeconfig and stores it encrypted on management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $kubeconfig = 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443';

        $this->reader->setKubeconfig('kuven-mgmt', $kubeconfig);

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        $this->action->handle($cluster, 'kuven-mgmt');

        $cluster->refresh();

        expect($cluster->kubeconfig)->toBe($kubeconfig);

        $raw = DB::table('management_clusters')
            ->where('id', $cluster->id)
            ->value('kubeconfig');

        expect($raw)->not->toBe($kubeconfig);
    });

test('throws when kubeconfig is not available for cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        expect(fn () => $this->action->handle($cluster, 'nonexistent'))
            ->toThrow(RuntimeException::class, "No kubeconfig found for cluster 'nonexistent'");
    });
