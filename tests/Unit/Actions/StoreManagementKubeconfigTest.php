<?php

declare(strict_types=1);

use App\Actions\StoreManagementKubeconfig;
use App\Models\ManagementCluster;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->action = $this->app->make(StoreManagementKubeconfig::class);
});

test('stores kubeconfig encrypted on management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $kubeconfig = 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443';

        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        $this->action->handle($cluster, $kubeconfig);

        $cluster->refresh();

        expect($cluster->kubeconfig)->toBe($kubeconfig);

        $raw = DB::table('management_clusters')
            ->where('id', $cluster->id)
            ->value('kubeconfig');

        expect($raw)->not->toBe($kubeconfig);
    });
