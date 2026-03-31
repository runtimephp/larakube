<?php

declare(strict_types=1);

use App\Data\NatGatewayConfigData;
use App\Exceptions\RetryStepException;
use App\Services\HetznerNatGatewayService;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

test('configures nat gateway successfully',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [
                        ['network' => 123, 'ip' => '10.0.0.2'],
                    ],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123' => Http::sequence()
                ->push([
                    'network' => [
                        'routes' => [],
                    ],
                ])
                ->push([
                    'network' => [
                        'subnets' => [
                            ['gateway' => '10.0.0.1'],
                        ],
                    ],
                ]),
            'api.hetzner.cloud/v1/networks/123/actions/add_route' => Http::response([
                'action' => ['id' => 1],
            ]),
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $service = new HetznerNatGatewayService('test-token', $processFactory);
        $result = $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));

        expect($result)->toBe('10.0.0.1');
    });

test('throws when server has no private ip',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [],
                ],
            ]),
        ]);

        $service = new HetznerNatGatewayService('test-token');
        $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));
    })->throws(RuntimeException::class, 'has no private IP');

test('skips route creation when route already exists',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [
                        ['network' => 123, 'ip' => '10.0.0.2'],
                    ],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123' => Http::sequence()
                ->push([
                    'network' => [
                        'routes' => [
                            ['destination' => '0.0.0.0/0', 'gateway' => '10.0.0.2'],
                        ],
                    ],
                ])
                ->push([
                    'network' => [
                        'subnets' => [
                            ['gateway' => '10.0.0.1'],
                        ],
                    ],
                ]),
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $service = new HetznerNatGatewayService('test-token', $processFactory);
        $result = $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));

        expect($result)->toBe('10.0.0.1');

        Http::assertNotSent(fn ($request) => str_contains((string) $request->url(), 'add_route'));
    });

test('throws when add route fails',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [
                        ['network' => 123, 'ip' => '10.0.0.2'],
                    ],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123' => Http::response([
                'network' => [
                    'routes' => [],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123/actions/add_route' => Http::response([
                'error' => ['message' => 'Route conflict'],
            ], 409),
        ]);

        $service = new HetznerNatGatewayService('test-token');
        $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));
    })->throws(RuntimeException::class, 'Route conflict');

test('throws retry when ssh connection refused during iptables config',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [
                        ['network' => 123, 'ip' => '10.0.0.2'],
                    ],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123' => Http::response([
                'network' => [
                    'routes' => [
                        ['destination' => '0.0.0.0/0', 'gateway' => '10.0.0.2'],
                    ],
                ],
            ]),
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "Connection refused" >&2 && exit 1');
            $process->run();

            return $process;
        };

        $service = new HetznerNatGatewayService('test-token', $processFactory);
        $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));
    })->throws(RetryStepException::class, 'Bastion SSH not ready');

test('throws runtime exception when iptables config fails',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [
                        ['network' => 123, 'ip' => '10.0.0.2'],
                    ],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123' => Http::response([
                'network' => [
                    'routes' => [
                        ['destination' => '0.0.0.0/0', 'gateway' => '10.0.0.2'],
                    ],
                ],
            ]),
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "iptables error" >&2 && exit 1');
            $process->run();

            return $process;
        };

        $service = new HetznerNatGatewayService('test-token', $processFactory);
        $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));
    })->throws(RuntimeException::class, 'Failed to configure iptables');

test('returns null when server lookup fails',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([], 500),
        ]);

        $service = new HetznerNatGatewayService('test-token');
        $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));
    })->throws(RuntimeException::class, 'has no private IP');

test('returns null gateway when subnet lookup fails',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [
                        ['network' => 123, 'ip' => '10.0.0.2'],
                    ],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123' => Http::sequence()
                ->push([
                    'network' => [
                        'routes' => [
                            ['destination' => '0.0.0.0/0', 'gateway' => '10.0.0.2'],
                        ],
                    ],
                ])
                ->push([], 500),
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $service = new HetznerNatGatewayService('test-token', $processFactory);
        $result = $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));

        expect($result)->toBeNull();
    });

test('route exists returns false on failed response',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            'api.hetzner.cloud/v1/servers/srv-1' => Http::response([
                'server' => [
                    'private_net' => [
                        ['network' => 123, 'ip' => '10.0.0.2'],
                    ],
                ],
            ]),
            'api.hetzner.cloud/v1/networks/123' => Http::sequence()
                ->push([], 500)
                ->push([
                    'network' => [
                        'subnets' => [
                            ['gateway' => '10.0.0.1'],
                        ],
                    ],
                ]),
            'api.hetzner.cloud/v1/networks/123/actions/add_route' => Http::response([
                'action' => ['id' => 1],
            ]),
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $service = new HetznerNatGatewayService('test-token', $processFactory);
        $result = $service->configure(new NatGatewayConfigData(
            networkId: '123',
            serverId: 'srv-1',
            serverPublicIp: '1.2.3.4',
            sshUser: 'root',
            sshPrivateKey: 'fake-key',
            networkCidr: '10.0.0.0/16',
        ));

        expect($result)->toBe('10.0.0.1');
    });
