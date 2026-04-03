<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\CapiInstallerService;
use RuntimeException;

final class InMemoryCapiInstallerService implements CapiInstallerService
{
    /** @var list<array{provider: string, kubeconfig: string}> */
    private array $installations = [];

    private bool $shouldFail = false;

    public function shouldFail(bool $fail = true): self
    {
        $this->shouldFail = $fail;

        return $this;
    }

    public function init(string $provider, string $kubeconfig): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException('Simulated CAPI installation failure');
        }

        $this->installations[] = [
            'provider' => $provider,
            'kubeconfig' => $kubeconfig,
        ];
    }

    /** @return list<array{provider: string, kubeconfig: string}> */
    public function installations(): array
    {
        return $this->installations;
    }
}
