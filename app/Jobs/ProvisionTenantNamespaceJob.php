<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\ProvisionTenantNamespace;
use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProvisionTenantNamespaceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Organization $organization,
    ) {
        $this->onQueue('kubernetes');
    }

    public function handle(ProvisionTenantNamespace $provisionTenantNamespace): void
    {
        $provisionTenantNamespace->handle($this->organization);

        Log::info("[{$this->organization->name}] Tenant namespace provisioned successfully.");
    }

    public function failed(Throwable $exception): void
    {
        Log::error("[{$this->organization->name}] Tenant namespace provisioning failed: {$exception->getMessage()}", [
            'exception' => $exception,
        ]);
    }
}
