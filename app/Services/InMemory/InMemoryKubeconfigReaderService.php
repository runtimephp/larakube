<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\KubeconfigReaderService;
use RuntimeException;

final class InMemoryKubeconfigReaderService implements KubeconfigReaderService
{
    /** @var array<string, string> */
    private array $kubeconfigs = [];

    public function setKubeconfig(string $clusterName, string $kubeconfig): self
    {
        $this->kubeconfigs[$clusterName] = $kubeconfig;

        return $this;
    }

    public function read(string $clusterName): string
    {
        return $this->kubeconfigs[$clusterName]
            ?? throw new RuntimeException("No kubeconfig found for cluster '{$clusterName}'");
    }
}
