<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Models\Infrastructure;
use App\Models\SshKey;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;

final readonly class RegisterSshKeys implements StepHandler
{
    public function __construct(
        private CloudProviderFactory $factory,
        private SshKeyQuery $sshKeyQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $provider = $infrastructure->cloudProvider;
        $sshKeyService = $this->factory->makeSshKeyService($provider->type, $provider->api_token);

        ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->unregistered()
            ->get()
            ->each(function (SshKey $key) use ($sshKeyService): void {
                $registered = $sshKeyService->register($key->name, $key->public_key);

                $key->update([
                    'external_ssh_key_id' => (string) $registered->externalId,
                ]);
            });
    }
}
