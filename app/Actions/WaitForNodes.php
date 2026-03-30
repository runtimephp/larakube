<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Queries\ServerQuery;
use App\Services\CloudProviderFactory;
use RuntimeException;

final readonly class WaitForNodes implements StepHandler
{
    public function __construct(
        private CloudProviderFactory $factory,
        private ServerQuery $serverQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $nodes = ($this->serverQuery)()
            ->byInfrastructure($infrastructure)
            ->builder()
            ->whereIn('role', [ServerRole::ControlPlane, ServerRole::Node])
            ->get();

        if ($nodes->isEmpty()) {
            throw new RuntimeException('No cluster nodes found for infrastructure: '.$infrastructure->id);
        }

        $provider = $infrastructure->cloudProvider;
        $serverService = $this->factory->makeServerService($provider->type, $provider->api_token);

        $allRunning = true;

        $nodes->each(function (Server $node) use ($serverService, &$allRunning): void {
            $current = $serverService->find($node->name);

            if ($current !== null) {
                $node->update(['status' => $current->status, 'ipv4' => $current->ipv4]);
            }

            $node->refresh();

            if ($node->status !== ServerStatus::Running) {
                $allRunning = false;
            }
        });

        if (! $allRunning) {
            throw new RetryStepException('Not all cluster nodes are running yet.');
        }
    }
}
