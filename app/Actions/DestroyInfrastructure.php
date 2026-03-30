<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\InfrastructureStatus;
use App\Models\Firewall;
use App\Models\Infrastructure;
use App\Models\Network;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;
use Throwable;

final readonly class DestroyInfrastructure
{
    /** @var list<string> */
    private array $failures;

    public function __construct(
        private CloudProviderFactory $factory,
        private ServerQuery $serverQuery,
        private SshKeyQuery $sshKeyQuery,
    ) {
        $this->failures = [];
    }

    /**
     * @return list<string> List of cloud API failure messages
     */
    public function handle(Infrastructure $infrastructure): array
    {
        $failures = [];

        $provider = $infrastructure->cloudProvider;
        $serverService = $this->factory->makeServerService($provider->type, $provider->api_token);
        $sshKeyService = $this->factory->makeSshKeyService($provider->type, $provider->api_token);
        $networkService = $this->factory->makeNetworkService($provider->type, $provider->api_token);
        $firewallService = $this->factory->makeFirewallService($provider->type, $provider->api_token);

        ($this->serverQuery)()
            ->byInfrastructure($infrastructure)
            ->get()
            ->each(function (Server $server) use ($serverService, &$failures): void {
                try {
                    $serverService->destroy($server->external_id);
                } catch (Throwable $e) {
                    $failures[] = "server {$server->name}: {$e->getMessage()}";
                }
                $server->delete();
            });

        $infrastructure->firewalls()->get()->each(function (Firewall $firewall) use ($firewallService, &$failures): void {
            if ($firewall->external_firewall_id) {
                try {
                    $firewallService->delete($firewall->external_firewall_id);
                } catch (Throwable $e) {
                    $failures[] = "firewall {$firewall->name}: {$e->getMessage()}";
                }
            }
            $firewall->delete();
        });

        $infrastructure->networks()->get()->each(function (Network $network) use ($networkService, &$failures): void {
            if ($network->external_network_id) {
                try {
                    $networkService->delete($network->external_network_id);
                } catch (Throwable $e) {
                    $failures[] = "network {$network->name}: {$e->getMessage()}";
                }
            }
            $network->delete();
        });

        ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->get()
            ->each(function (SshKey $key) use ($sshKeyService, &$failures): void {
                if ($key->external_ssh_key_id) {
                    try {
                        $sshKeyService->delete($key->external_ssh_key_id);
                    } catch (Throwable $e) {
                        $failures[] = "SSH key {$key->name}: {$e->getMessage()}";
                    }
                }
                $key->delete();
            });

        $infrastructure->update([
            'status' => InfrastructureStatus::Destroyed,
            'provisioning_step' => null,
            'provisioning_phase' => null,
        ]);

        return $failures;
    }
}
