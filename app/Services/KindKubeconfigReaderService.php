<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\KubeconfigReaderService;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/** @codeCoverageIgnore */
final class KindKubeconfigReaderService implements KubeconfigReaderService
{
    public function read(string $clusterName): string
    {
        $result = Process::run("kind get kubeconfig --name {$clusterName}");

        if ($result->failed()) {
            throw new RuntimeException("Failed to read kubeconfig for cluster '{$clusterName}': {$result->errorOutput()}");
        }

        return $result->output();
    }
}
