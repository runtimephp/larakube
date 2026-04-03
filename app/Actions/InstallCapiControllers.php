<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\CapiInstallerService;

final readonly class InstallCapiControllers
{
    public function __construct(
        private CapiInstallerService $service,
    ) {}

    public function handle(string $provider, string $kubeconfig): void
    {
        $this->service->init($provider, $kubeconfig);
    }
}
