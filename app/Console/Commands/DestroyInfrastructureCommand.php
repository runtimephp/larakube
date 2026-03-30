<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InfrastructureStatus;
use App\Models\Firewall;
use App\Models\Infrastructure;
use App\Models\Network;
use App\Models\Server;
use App\Models\SshKey;
use App\Services\CloudProviderFactory;
use Throwable;

final class DestroyInfrastructureCommand extends AuthenticatedCommand
{
    protected $signature = 'infrastructure:destroy';

    protected $description = 'Destroy all cloud resources for the selected infrastructure';

    protected bool $requiresOrganization = true;

    protected bool $requiresInfrastructure = true;

    private int $failures = 0;

    public function handleCommand(CloudProviderFactory $factory): int
    {
        /** @var Infrastructure|null $infrastructure */
        $infrastructure = Infrastructure::query()->find($this->infrastructure->id);

        if ($infrastructure === null) {
            $this->components->error('Infrastructure not found.');

            return self::FAILURE;
        }

        if (! $this->confirm('This will destroy ALL resources for infrastructure "'.$infrastructure->name.'". Continue?')) {
            $this->components->error('Aborted.');

            return self::FAILURE;
        }

        $provider = $infrastructure->cloudProvider;
        $serverService = $factory->makeServerService($provider->type, $provider->api_token);
        $sshKeyService = $factory->makeSshKeyService($provider->type, $provider->api_token);
        $networkService = $factory->makeNetworkService($provider->type, $provider->api_token);
        $firewallService = $factory->makeFirewallService($provider->type, $provider->api_token);

        $this->components->info('Destroying servers...');
        Server::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->get()
            ->each(function (Server $server) use ($serverService): void {
                $this->tryCloudDelete(
                    fn (): bool => $serverService->destroy($server->external_id),
                    "server: {$server->name}",
                );
                $server->delete();
            });

        $this->components->info('Destroying firewalls...');
        Firewall::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->get()
            ->each(function (Firewall $firewall) use ($firewallService): void {
                if ($firewall->external_firewall_id) {
                    $this->tryCloudDelete(
                        fn (): bool => $firewallService->delete($firewall->external_firewall_id),
                        "firewall: {$firewall->name}",
                    );
                }
                $firewall->delete();
            });

        $this->components->info('Destroying networks...');
        Network::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->get()
            ->each(function (Network $network) use ($networkService): void {
                if ($network->external_network_id) {
                    $this->tryCloudDelete(
                        fn (): bool => $networkService->delete($network->external_network_id),
                        "network: {$network->name}",
                    );
                }
                $network->delete();
            });

        $this->components->info('Destroying SSH keys...');
        SshKey::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->get()
            ->each(function (SshKey $key) use ($sshKeyService): void {
                if ($key->external_ssh_key_id) {
                    $this->tryCloudDelete(
                        fn (): bool => $sshKeyService->delete($key->external_ssh_key_id),
                        "SSH key: {$key->name}",
                    );
                }
                $key->delete();
            });

        $infrastructure->update([
            'status' => InfrastructureStatus::Destroyed,
            'provisioning_step' => null,
            'provisioning_phase' => null,
        ]);

        if ($this->failures > 0) {
            $this->components->warn("Infrastructure \"{$infrastructure->name}\" destroyed with {$this->failures} cloud API failure(s). DB records cleaned. Check cloud console for orphaned resources.");
        } else {
            $this->components->info("Infrastructure \"{$infrastructure->name}\" destroyed successfully.");
        }

        return self::SUCCESS;
    }

    /**
     * @param  callable(): bool  $callback
     */
    private function tryCloudDelete(callable $callback, string $resource): void
    {
        try {
            $callback();
            $this->line("  Deleted {$resource}");
        } catch (Throwable $e) {
            $this->failures++;
            $this->components->warn("  Failed to delete {$resource}: {$e->getMessage()}");
        }
    }
}
