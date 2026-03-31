<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NatGatewayService;
use App\Data\NatGatewayConfigData;

final readonly class MultipassNatGatewayService implements NatGatewayService
{
    public function configure(NatGatewayConfigData $config): ?string
    {
        // No-op: Multipass VMs share host network
        return null;
    }
}
