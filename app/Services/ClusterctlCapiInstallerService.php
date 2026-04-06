<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CapiInstallerService;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/** @codeCoverageIgnore */
final class ClusterctlCapiInstallerService implements CapiInstallerService
{
    private const CAPI_VERSION = 'v1.8.10';

    public function init(string $provider, string $kubeconfig): void
    {
        $version = self::CAPI_VERSION;

        $result = Process::timeout(300)->run(
            "clusterctl init --core cluster-api:{$version} --bootstrap kubeadm:{$version} --control-plane kubeadm:{$version} --infrastructure {$provider} --kubeconfig {$kubeconfig}"
        );

        if ($result->failed()) {
            throw new RuntimeException("Failed to install CAPI controllers: {$result->errorOutput()}");
        }
    }
}
