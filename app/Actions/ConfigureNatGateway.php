<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\NatGatewayConfigData;
use App\Enums\ServerRole;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;
use Illuminate\Support\Facades\Cache;

final readonly class ConfigureNatGateway
{
    public function __construct(
        private CloudProviderFactory $factory,
        private ServerQuery $serverQuery,
        private SshKeyQuery $sshKeyQuery,
    ) {}

    public static function getGatewayIp(Infrastructure $infrastructure): ?string
    {
        return Cache::get("infrastructure.{$infrastructure->id}.gateway_ip");
    }

    public function handle(Infrastructure $infrastructure): void
    {
        $network = $infrastructure->networks()->first();

        if ($network === null || $network->external_network_id === null) {
            return;
        }

        $bastion = ($this->serverQuery)()
            ->byInfrastructure($infrastructure)
            ->byRole(ServerRole::Bastion)
            ->firstOrFail();

        $bastionKey = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->byPurpose(SshKeyPurpose::Bastion)
            ->firstOrFail();

        $provider = $infrastructure->cloudProvider;
        $natService = $this->factory->makeNatGatewayService($provider->type, $provider->api_token);

        $gatewayIp = $natService->configure(new NatGatewayConfigData(
            networkId: $network->external_network_id,
            serverId: $bastion->external_id,
            serverPublicIp: $bastion->ipv4,
            sshUser: $provider->type->sshUser(),
            sshPrivateKey: $bastionKey->private_key,
            networkCidr: $network->cidr ?? '10.0.0.0/16',
        ));

        if ($gatewayIp !== null) {
            Cache::put("infrastructure.{$infrastructure->id}.gateway_ip", $gatewayIp, now()->addHours(2));
        }
    }
}
