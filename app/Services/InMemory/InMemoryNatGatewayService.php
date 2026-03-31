<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\NatGatewayService;
use App\Data\NatGatewayConfigData;

final class InMemoryNatGatewayService implements NatGatewayService
{
    public bool $configured = false;

    public function configure(NatGatewayConfigData $config): ?string
    {
        $this->configured = true;

        return null;
    }
}
