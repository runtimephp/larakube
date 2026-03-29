<?php

declare(strict_types=1);

use App\Services\SshKeyGenerator;
use Symfony\Component\Process\Process;

test('generates ed25519 keypair', function (): void {
    $privateKey = "-----BEGIN OPENSSH PRIVATE KEY-----\nfake-private-key\n-----END OPENSSH PRIVATE KEY-----\n";
    $publicKey = "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIFake kuven@bastion\n";

    $processFactory = function (array $command) use ($privateKey, $publicKey): Process {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->andReturn(true);

        // ssh-keygen writes files — the generator reads them after
        // We simulate by checking which command was called
        if ($command[0] === 'ssh-keygen') {
            // Extract the file path from -f argument
            $fileIndex = array_search('-f', $command);
            $filePath = $command[$fileIndex + 1];

            // Write fake key files so the generator can read them
            file_put_contents($filePath, $privateKey);
            file_put_contents($filePath.'.pub', $publicKey);
        }

        return $process;
    };

    $generator = new SshKeyGenerator($processFactory);
    $keypair = $generator->generate('kuven@bastion');

    expect($keypair->privateKey)->toBe($privateKey)
        ->and($keypair->publicKey)->toBe($publicKey);
});

test('throws on ssh-keygen failure', function (): void {
    $processFactory = function (array $command): Process {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('run')->once();
        $process->shouldReceive('isSuccessful')->andReturn(false);
        $process->shouldReceive('getErrorOutput')->andReturn('ssh-keygen failed');

        return $process;
    };

    $generator = new SshKeyGenerator($processFactory);
    $generator->generate('kuven@bastion');
})->throws(RuntimeException::class, 'Failed to generate SSH keypair');
