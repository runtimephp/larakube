<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\NatGatewayConfigData;

/**
 * @see ADR-0005, ADR-0009 — Write methods to be removed; refactoring to CloudManager driver pattern
 */
interface NatGatewayService
{
    /**
     * @return string|null The gateway private IP (for providers that need node default routes)
     */
    public function configure(NatGatewayConfigData $config): ?string;
}
