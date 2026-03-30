<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\CloudProviderType;
use App\Models\Infrastructure;
use App\Models\SshKey;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;
use RuntimeException;

final readonly class RegisterSshKeys implements StepHandler
{
    public function __construct(
        private CloudProviderFactory $factory,
        private SshKeyQuery $sshKeyQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $provider = $infrastructure->cloudProvider;

        if ($provider->api_token === null && $provider->type !== CloudProviderType::Multipass) {
            throw new RuntimeException("Cloud provider [{$provider->name}] has no API token configured.");
        }

        $sshKeyService = $this->factory->makeSshKeyService($provider->type, $provider->api_token);

        ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->unregistered()
            ->get()
            ->each(function (SshKey $key) use ($sshKeyService): void {
                $registered = $sshKeyService->register($key->name, $key->public_key);
                $externalId = (string) $registered->externalId;

                if ($externalId === '') {
                    throw new RuntimeException('Cloud provider returned an empty external SSH key ID.');
                }

                $key->update([
                    'external_ssh_key_id' => $externalId,
                    'fingerprint' => $registered->fingerprint,
                ]);
            });
    }
}
