<?php

declare(strict_types=1);

use App\Data\NatGatewayConfigData;
use App\Services\InMemory\InMemoryNatGatewayService;

test('configure sets configured flag and returns null', function (): void {
    $service = new InMemoryNatGatewayService();

    expect($service->configured)->toBeFalse();

    $result = $service->configure(new NatGatewayConfigData(
        networkId: 'net-1',
        serverId: 'srv-1',
        serverPublicIp: '192.168.1.1',
        sshUser: 'root',
        sshPrivateKey: 'key',
        networkCidr: '10.0.0.0/16',
    ));

    expect($result)->toBeNull()
        ->and($service->configured)->toBeTrue();
});
