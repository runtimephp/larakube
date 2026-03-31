<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Data\CreateServerData;
use App\Enums\CloudProviderType;
use App\Enums\ClusterTopology;
use App\Enums\ServerRole;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudInitGenerator;

final readonly class CreateControlPlaneNodes implements StepHandler
{
    public function __construct(
        private CreateServer $createServer,
        private CloudInitGenerator $cloudInit,
        private ServerQuery $serverQuery,
        private SshKeyQuery $sshKeyQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        if (($this->serverQuery)()->byInfrastructure($infrastructure)->byRole(ServerRole::ControlPlane)->exists()) {
            return;
        }

        $provider = $infrastructure->cloudProvider;
        $spec = $provider->type->controlPlaneSpec();
        $topology = self::topologyFor($provider->type);

        $sshKeyIds = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->get()
            ->whereNotNull('external_ssh_key_id')
            ->pluck('external_ssh_key_id')
            ->map(fn (string $id): int|string => is_numeric($id) ? (int) $id : $id)
            ->all();

        $networkId = $infrastructure->networks()->first()?->external_network_id;

        $nodeKey = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->byPurpose(SshKeyPurpose::Node)
            ->firstOrFail();

        $gatewayIp = ConfigureNatGateway::getGatewayIp($infrastructure);
        $dnsServers = $provider->type->dnsServers();
        $nodeCloudInit = $this->cloudInit->node($nodeKey->public_key, $gatewayIp, $dnsServers);

        $nodeCount = $topology === ClusterTopology::Ha ? 3 : 1;

        for ($i = 1; $i <= $nodeCount; $i++) {
            $this->createServer->handle($provider, new CreateServerData(
                name: "{$infrastructure->name}-cp-{$i}",
                type: $spec->type,
                image: $spec->image,
                region: $spec->region,
                infrastructure_id: $infrastructure->id,
                role: ServerRole::ControlPlane,
                cpus: $spec->cpus,
                memory: $spec->memory,
                disk: $spec->disk,
                sshKeyIds: $sshKeyIds,
                cloudInit: $nodeCloudInit,
                publicIp: false,
                networkId: $networkId,
            ));
        }
    }

    private static function topologyFor(CloudProviderType $type): ClusterTopology
    {
        return match ($type) {
            CloudProviderType::Multipass => ClusterTopology::SingleCp,
            default => ClusterTopology::Ha,
        };
    }
}
