<?php

declare(strict_types=1);

use App\Actions\RegisterSshKeys;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\SshKey;
use App\Queries\SshKeyQuery;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemorySshKeyService;

test('registers bastion and node public keys with cloud provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'cloud_provider_id' => $provider->id,
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        SshKey::factory()->node()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $sshKeyService = new InMemorySshKeyService();

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeSshKeyService')
            ->with($provider->type, $provider->api_token)
            ->once()
            ->andReturn($sshKeyService);

        $action = new RegisterSshKeys($factory, new SshKeyQuery());
        $action->handle($infrastructure);

        expect($sshKeyService->list())->toHaveCount(2);

        $keys = SshKey::where('infrastructure_id', $infrastructure->id)->get();

        expect($keys->every(fn (SshKey $key): bool => $key->external_ssh_key_id !== null))->toBeTrue();
    });
