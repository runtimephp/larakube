<?php

declare(strict_types=1);

use App\Data\NatGatewayConfigData;
use App\Services\MultipassNatGatewayService;

test('configure returns null', function (): void {
    $service = new MultipassNatGatewayService();

    $result = $service->configure(new NatGatewayConfigData(
        networkId: 'net-1',
        serverId: 'srv-1',
        serverPublicIp: '192.168.1.1',
        sshUser: 'ubuntu',
        sshPrivateKey: 'key',
        networkCidr: '10.0.0.0/16',
    ));

    expect($result)->toBeNull();
});
