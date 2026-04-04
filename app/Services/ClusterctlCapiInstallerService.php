<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CapiInstallerService;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/** @codeCoverageIgnore */
final class ClusterctlCapiInstallerService implements CapiInstallerService
{
    public function init(string $provider, string $kubeconfig): void
    {
        $result = Process::run("clusterctl init --infrastructure {$provider} --kubeconfig {$kubeconfig}");

        if ($result->failed()) {
            throw new RuntimeException("Failed to install CAPI controllers: {$result->errorOutput()}");
        }
    }
}
