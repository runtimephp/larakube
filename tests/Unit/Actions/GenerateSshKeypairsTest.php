<?php

declare(strict_types=1);

use App\Actions\CreateSshKey;
use App\Actions\GenerateSshKeypairs;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\SshKey;
use App\Queries\SshKeyQuery;
use App\Services\SshKeyGenerator;
use Symfony\Component\Process\Process;

test('returns early when both keys already exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        SshKey::factory()->node()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $generator = new SshKeyGenerator(function (array $command): Process {
            throw new RuntimeException('Should not be called');
        });

        $action = new GenerateSshKeypairs($generator, new CreateSshKey(), new SshKeyQuery());
        $action->handle($infrastructure);

        expect(SshKey::where('infrastructure_id', $infrastructure->id)->count())->toBe(2);
    });

test('generates bastion and node keypairs and stores them',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $generator = new SshKeyGenerator(function (array $command): Process {
            $fileIndex = array_search('-f', $command, true);

            if ($fileIndex === false || ! isset($command[$fileIndex + 1])) {
                throw new RuntimeException('Test double: ssh-keygen command missing -f argument');
            }

            $filePath = $command[$fileIndex + 1];

            $commentIndex = array_search('-C', $command, true);
            $comment = ($commentIndex !== false && isset($command[$commentIndex + 1]))
                ? $command[$commentIndex + 1]
                : 'test';

            file_put_contents($filePath, "-----BEGIN OPENSSH PRIVATE KEY-----\nfake-{$comment}\n-----END OPENSSH PRIVATE KEY-----\n");
            file_put_contents($filePath.'.pub', "ssh-ed25519 AAAA... {$comment}\n");

            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        });

        $action = new GenerateSshKeypairs($generator, new CreateSshKey(), new SshKeyQuery());
        $action->handle($infrastructure);

        $bastionKey = SshKey::where('infrastructure_id', $infrastructure->id)
            ->where('purpose', SshKeyPurpose::Bastion)
            ->first();

        $nodeKey = SshKey::where('infrastructure_id', $infrastructure->id)
            ->where('purpose', SshKeyPurpose::Node)
            ->first();

        expect($bastionKey)->not->toBeNull()
            ->and($bastionKey->public_key)->toContain('ssh-ed25519')
            ->and($bastionKey->private_key)->toContain('BEGIN OPENSSH PRIVATE KEY')
            ->and($bastionKey->purpose)->toBe(SshKeyPurpose::Bastion)
            ->and($nodeKey)->not->toBeNull()
            ->and($nodeKey->public_key)->toContain('ssh-ed25519')
            ->and($nodeKey->private_key)->toContain('BEGIN OPENSSH PRIVATE KEY')
            ->and($nodeKey->purpose)->toBe(SshKeyPurpose::Node);
    });
