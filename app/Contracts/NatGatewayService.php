<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\NatGatewayConfigData;

interface NatGatewayService
{
    /**
     * @return string|null The gateway private IP (for providers that need node default routes)
     */
    public function configure(NatGatewayConfigData $config): ?string;
}
