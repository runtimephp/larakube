<?php

declare(strict_types=1);

namespace App\Data;

final readonly class NatGatewayConfigData
{
    public function __construct(
        public string $networkId,
        public string $serverId,
        public string $serverPublicIp,
        public string $sshUser,
        public string $sshPrivateKey,
        public string $networkCidr,
    ) {}
}
