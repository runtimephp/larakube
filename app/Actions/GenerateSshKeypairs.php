<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\SshKey;
use App\Queries\SshKeyQuery;
use App\Services\SshKeyGenerator;

final readonly class GenerateSshKeypairs implements StepHandler
{
    public function __construct(
        private SshKeyGenerator $generator,
        private SshKeyQuery $sshKeyQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $hasBastionKey = ($this->sshKeyQuery)()->byInfrastructure($infrastructure)->byPurpose(SshKeyPurpose::Bastion)->exists();
        $hasNodeKey = ($this->sshKeyQuery)()->byInfrastructure($infrastructure)->byPurpose(SshKeyPurpose::Node)->exists();

        if ($hasBastionKey && $hasNodeKey) {
            return;
        }

        $bastionKeypair = $this->generator->generate('kuven@bastion');

        SshKey::query()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => "{$infrastructure->name}-bastion",
            'fingerprint' => md5($bastionKeypair->publicKey),
            'public_key' => mb_trim($bastionKeypair->publicKey),
            'private_key' => $bastionKeypair->privateKey,
            'purpose' => SshKeyPurpose::Bastion,
        ]);

        $nodeKeypair = $this->generator->generate('kuven@node');

        SshKey::query()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => "{$infrastructure->name}-node",
            'fingerprint' => md5($nodeKeypair->publicKey),
            'public_key' => mb_trim($nodeKeypair->publicKey),
            'private_key' => $nodeKeypair->privateKey,
            'purpose' => SshKeyPurpose::Node,
        ]);
    }
}
