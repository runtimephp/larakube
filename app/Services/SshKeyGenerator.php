<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\SshKeypairData;
use Closure;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * @see ADR-0005 — Superseded by CAPI; scheduled for removal
 */
final readonly class SshKeyGenerator
{
    /** @var Closure(list<string>): Process */
    private Closure $processFactory;

    /**
     * @param  Closure(list<string>): Process|null  $processFactory
     */
    public function __construct(?Closure $processFactory = null)
    {
        $this->processFactory = $processFactory ?? fn (array $command): Process => new Process($command);
    }

    public function generate(string $comment = ''): SshKeypairData
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'kuven_ssh_');

        if ($tempFile === false) { // @codeCoverageIgnore
            throw new RuntimeException('Failed to create temporary file for SSH keypair generation.'); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        // Remove the temp file so ssh-keygen can create it
        unlink($tempFile);

        try {
            $process = ($this->processFactory)([
                'ssh-keygen',
                '-t', 'ed25519',
                '-f', $tempFile,
                '-N', '',
                '-C', $comment,
            ]);

            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('Failed to generate SSH keypair: '.$process->getErrorOutput());
            }

            $privateKey = file_get_contents($tempFile);
            $publicKey = file_get_contents($tempFile.'.pub');

            if ($privateKey === false || $publicKey === false) { // @codeCoverageIgnore
                throw new RuntimeException('Failed to read generated SSH keypair files.'); // @codeCoverageIgnore
            } // @codeCoverageIgnore

            return new SshKeypairData(
                publicKey: $publicKey,
                privateKey: $privateKey,
            );
        } finally {
            @unlink($tempFile);
            @unlink($tempFile.'.pub');
        }
    }
}
