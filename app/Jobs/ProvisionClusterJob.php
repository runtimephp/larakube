<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\MarkClusterFailed;
use App\Actions\ProvisionCluster;
use App\Data\CreateClusterManifestData;
use App\Models\KubernetesCluster;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProvisionClusterJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public KubernetesCluster $cluster,
        public CreateClusterManifestData $data,
    ) {
        $this->onQueue('kubernetes');
    }

    public function handle(ProvisionCluster $provisionCluster): void
    {
        $provisionCluster->handle($this->data);
    }

    public function failed(Throwable $exception): void
    {
        try {
            app(MarkClusterFailed::class)->handle($this->cluster);
        } catch (Throwable $markFailedException) {
            Log::error("[{$this->cluster->name}] Failed to mark cluster as failed: {$markFailedException->getMessage()}", [
                'exception' => $markFailedException,
            ]);
        }

        Log::error("[{$this->cluster->name}] Cluster provisioning failed: {$exception->getMessage()}", [
            'exception' => $exception,
        ]);
    }
}
