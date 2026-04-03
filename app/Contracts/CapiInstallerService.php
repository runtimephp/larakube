<?php

declare(strict_types=1);

namespace App\Contracts;

interface CapiInstallerService
{
    public function init(string $provider, string $kubeconfig): void;
}
