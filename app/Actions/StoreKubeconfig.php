<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final readonly class StoreKubeconfig implements StepHandler
{
    public function handle(Infrastructure $infrastructure): void
    {
        $path = "kubeconfigs/{$infrastructure->id}.conf";

        $kubeconfig = Storage::disk('local')->get($path);

        if ($kubeconfig === null) {
            throw new RuntimeException("Kubeconfig not found at storage path: {$path}");
        }

        /** @var KubernetesCluster $cluster */
        $cluster = $infrastructure->kubernetesClusters()->firstOrCreate(
            ['infrastructure_id' => $infrastructure->id],
            [
                'name' => "{$infrastructure->name}-cluster",
                'version' => '1.31',
                'status' => 'healthy',
            ],
        );

        $cluster->update([
            'kubeconfig' => $kubeconfig,
            'api_endpoint' => $this->extractApiEndpoint($kubeconfig),
            'pod_cidr' => '10.244.0.0/16',
            'service_cidr' => '10.96.0.0/12',
        ]);

        Storage::disk('local')->delete($path);
    }

    private function extractApiEndpoint(string $kubeconfig): ?string
    {
        if (preg_match('/server:\s*(.+)/', $kubeconfig, $matches)) {
            return mb_trim($matches[1]);
        }

        return null;
    }
}
