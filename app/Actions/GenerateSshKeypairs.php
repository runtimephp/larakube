<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Queries\SshKeyQuery;
use App\Services\SshKeyGenerator;

final readonly class GenerateSshKeypairs implements StepHandler
{
    public function __construct(
        private SshKeyGenerator $generator,
        private CreateSshKey $createSshKey,
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

        $this->createSshKey->handle(
            infrastructure: $infrastructure,
            name: "{$infrastructure->name}-bastion",
            fingerprint: md5($bastionKeypair->publicKey),
            publicKey: mb_trim($bastionKeypair->publicKey),
            purpose: SshKeyPurpose::Bastion,
            privateKey: $bastionKeypair->privateKey,
        );

        $nodeKeypair = $this->generator->generate('kuven@node');

        $this->createSshKey->handle(
            infrastructure: $infrastructure,
            name: "{$infrastructure->name}-node",
            fingerprint: md5($nodeKeypair->publicKey),
            publicKey: mb_trim($nodeKeypair->publicKey),
            purpose: SshKeyPurpose::Node,
            privateKey: $nodeKeypair->privateKey,
        );
    }
}
