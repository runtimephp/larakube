<?php

declare(strict_types=1);

use App\Services\SshKeyGenerator;
use Symfony\Component\Process\Process;

test('generates keypair and displays public key',
    /**
     * @throws Throwable
     */
    function (): void {
        $privateKey = "-----BEGIN OPENSSH PRIVATE KEY-----\nfake\n-----END OPENSSH PRIVATE KEY-----\n";
        $publicKey = "ssh-ed25519 AAAA... kuven@test\n";

        $processFactory = function (array $command) use ($privateKey, $publicKey): Process {
            $fileIndex = array_search('-f', $command);
            $filePath = $command[$fileIndex + 1];
            file_put_contents($filePath, $privateKey);
            file_put_contents($filePath.'.pub', $publicKey);

            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $this->app->instance(SshKeyGenerator::class, new SshKeyGenerator($processFactory));

        $this->artisan('ssh:generate-keypair')
            ->expectsOutputToContain('ssh-ed25519 AAAA...')
            ->expectsOutputToContain('Private key was NOT saved')
            ->expectsOutputToContain('Keypair generated successfully')
            ->assertSuccessful();
    });

test('saves private key to file with save-to option',
    /**
     * @throws Throwable
     */
    function (): void {
        $privateKey = "-----BEGIN OPENSSH PRIVATE KEY-----\nfake-private\n-----END OPENSSH PRIVATE KEY-----\n";
        $publicKey = "ssh-ed25519 BBBB... kuven@bastion\n";

        $processFactory = function (array $command) use ($privateKey, $publicKey): Process {
            $fileIndex = array_search('-f', $command);
            $filePath = $command[$fileIndex + 1];
            file_put_contents($filePath, $privateKey);
            file_put_contents($filePath.'.pub', $publicKey);

            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $this->app->instance(SshKeyGenerator::class, new SshKeyGenerator($processFactory));

        $path = sys_get_temp_dir().'/kuven-test-key-'.uniqid();

        try {
            $this->artisan("ssh:generate-keypair kuven@bastion --save-to={$path}")
                ->expectsOutputToContain("Private key saved to: {$path}")
                ->expectsOutputToContain("Public key saved to: {$path}.pub")
                ->assertSuccessful();

            expect(file_exists($path))->toBeTrue()
                ->and(file_get_contents($path))->toContain('BEGIN OPENSSH PRIVATE KEY')
                ->and(file_exists($path.'.pub'))->toBeTrue()
                ->and(file_get_contents($path.'.pub'))->toContain('ssh-ed25519 BBBB...')
                ->and(decoct(fileperms($path) & 0777))->toBe('600');
        } finally {
            @unlink($path);
            @unlink($path.'.pub');
        }
    });
