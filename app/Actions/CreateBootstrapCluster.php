<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\BootstrapClusterService;

final readonly class CreateBootstrapCluster
{
    public function __construct(
        private BootstrapClusterService $service,
    ) {}

    public function handle(string $name): void
    {
        if ($this->service->exists($name)) {
            return;
        }

        $this->service->create($name);
    }
}
