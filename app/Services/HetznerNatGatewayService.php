<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NatGatewayService;
use App\Data\NatGatewayConfigData;
use App\Exceptions\RetryStepException;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Process\Process;

final readonly class HetznerNatGatewayService implements NatGatewayService
{
    /** @var Closure(list<string>): Process */
    private Closure $processFactory;

    /**
     * @param  Closure(list<string>): Process|null  $processFactory
     */
    public function __construct(
        private string $token,
        ?Closure $processFactory = null,
    ) {
        $this->processFactory = $processFactory ?? fn (array $command): Process => new Process($command);
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function configure(NatGatewayConfigData $config): ?string
    {
        $bastionIp = $this->getServerPrivateIp($config->serverId, $config->networkId);

        if ($bastionIp === null) {
            throw new RuntimeException("Server {$config->serverId} has no private IP on network {$config->networkId}.");
        }

        $this->addNetworkRoute($config->networkId, $bastionIp);
        $this->configureIptables($config);

        return $this->getSubnetGateway($config->networkId);
    }

    private function addNetworkRoute(string $networkId, string $gatewayIp): void
    {
        if ($this->routeExists($networkId, '0.0.0.0/0')) {
            return;
        }

        $response = Http::withToken($this->token)
            ->post("https://api.hetzner.cloud/v1/networks/{$networkId}/actions/add_route", [
                'destination' => '0.0.0.0/0',
                'gateway' => $gatewayIp,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to add NAT route on Hetzner.'));
        }
    }

    private function routeExists(string $networkId, string $destination): bool
    {
        $response = Http::withToken($this->token)
            ->get("https://api.hetzner.cloud/v1/networks/{$networkId}");

        if (! $response->successful()) {
            return false;
        }

        foreach ($response->json('network.routes', []) as $route) {
            if ($route['destination'] === $destination) {
                return true;
            }
        }

        return false;
    }

    private function configureIptables(NatGatewayConfigData $config): void
    {
        $keyFile = tempnam(sys_get_temp_dir(), 'kuven_nat_');

        if ($keyFile === false || file_put_contents($keyFile, $config->sshPrivateKey) === false) { // @codeCoverageIgnore
            throw new RuntimeException('Failed to write SSH key for NAT configuration.'); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        chmod($keyFile, 0600);

        try {
            $commands = implode(' && ', [
                'sysctl -w net.ipv4.ip_forward=1',
                "echo 'net.ipv4.ip_forward=1' > /etc/sysctl.d/99-nat.conf",
                "iptables -t nat -C POSTROUTING -s {$config->networkCidr} -o eth0 -j MASQUERADE 2>/dev/null || iptables -t nat -A POSTROUTING -s {$config->networkCidr} -o eth0 -j MASQUERADE",
            ]);

            $process = ($this->processFactory)([
                'ssh',
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                '-o', 'BatchMode=yes',
                '-i', $keyFile,
                "{$config->sshUser}@{$config->serverPublicIp}",
                $commands,
            ]);

            $process->setTimeout(30);
            $process->run();

            if (! $process->isSuccessful()) {
                $error = $process->getErrorOutput();

                if (str_contains($error, 'Connection refused') || str_contains($error, 'Connection timed out')) {
                    throw new RetryStepException('Bastion SSH not ready for NAT config: '.$error);
                }

                throw new RuntimeException('Failed to configure iptables NAT on bastion: '.$error);
            }
        } finally {
            @unlink($keyFile);
        }
    }

    private function getSubnetGateway(string $networkId): ?string
    {
        $response = Http::withToken($this->token)
            ->get("https://api.hetzner.cloud/v1/networks/{$networkId}");

        if (! $response->successful()) {
            return null;
        }

        $subnets = $response->json('network.subnets', []);

        return $subnets[0]['gateway'] ?? null;
    }

    private function getServerPrivateIp(string $serverId, string $networkId): ?string
    {
        $response = Http::withToken($this->token)
            ->get("https://api.hetzner.cloud/v1/servers/{$serverId}");

        if (! $response->successful()) {
            return null;
        }

        foreach ($response->json('server.private_net', []) as $net) {
            if ((string) $net['network'] === $networkId) {
                return $net['ip'];
            }
        }

        return null;
    }
}
