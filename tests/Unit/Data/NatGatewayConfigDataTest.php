<?php

declare(strict_types=1);

use App\Data\NatGatewayConfigData;

test('constructor sets all properties', function (): void {
    $data = new NatGatewayConfigData(
        networkId: 'net-123',
        serverId: 'srv-456',
        serverPublicIp: '192.168.1.1',
        sshUser: 'root',
        sshPrivateKey: 'private-key-content',
        networkCidr: '10.0.0.0/16',
    );

    expect($data)
        ->networkId->toBe('net-123')
        ->serverId->toBe('srv-456')
        ->serverPublicIp->toBe('192.168.1.1')
        ->sshUser->toBe('root')
        ->sshPrivateKey->toBe('private-key-content')
        ->networkCidr->toBe('10.0.0.0/16');
});
