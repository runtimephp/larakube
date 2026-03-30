<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Queries\ServerQuery;
use App\Services\CloudProviderFactory;
use RuntimeException;

final readonly class WaitForBastion implements StepHandler
{
    public function __construct(
        private CloudProviderFactory $factory,
        private ServerQuery $serverQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $bastion = ($this->serverQuery)()
            ->byInfrastructure($infrastructure)
            ->byRole(ServerRole::Bastion)
            ->first();

        if ($bastion === null) {
            throw new RuntimeException('No bastion server found for infrastructure: '.$infrastructure->id);
        }

        $provider = $infrastructure->cloudProvider;
        $serverService = $this->factory->makeServerService($provider->type, $provider->api_token);

        $current = $serverService->find($bastion->name);

        if ($current !== null) {
            $bastion->update(['status' => $current->status, 'ipv4' => $current->ipv4]);
        }

        if ($bastion->fresh()->status !== ServerStatus::Running) {
            throw new RetryStepException('Bastion server is not yet running. Current status: '.$bastion->status->value);
        }
    }
}
