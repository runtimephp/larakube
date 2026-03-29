<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SshKeyGenerator;
use Illuminate\Console\Command;

final class GenerateSshKeypairCommand extends Command
{
    protected $signature = 'ssh:generate-keypair
        {comment=kuven@test}
        {--save-to= : Path to save the private key file}';

    protected $description = 'Generate an Ed25519 SSH keypair for testing';

    public function handle(SshKeyGenerator $generator): int
    {
        $comment = $this->argument('comment');

        $this->info("Generating Ed25519 keypair with comment: {$comment}");

        $keypair = $generator->generate($comment);

        $this->newLine();
        $this->info('Public key:');
        $this->line($keypair->publicKey);

        $saveTo = $this->option('save-to');

        if ($saveTo !== null) {
            $expandedPath = str_replace('~', getenv('HOME') ?: '', $saveTo);

            if (file_put_contents($expandedPath, $keypair->privateKey) === false) {
                $this->error("Failed to write private key to: {$expandedPath}");

                return self::FAILURE;
            }

            if (! chmod($expandedPath, 0600)) {
                $this->error("Failed to set permissions on: {$expandedPath}");

                return self::FAILURE;
            }

            if (file_put_contents($expandedPath.'.pub', $keypair->publicKey) === false) {
                $this->error("Failed to write public key to: {$expandedPath}.pub");

                return self::FAILURE;
            }

            $this->info("Private key saved to: {$expandedPath}");
            $this->info("Public key saved to: {$expandedPath}.pub");
        } else {
            $this->info('Private key (first 3 lines):');
            $lines = explode("\n", $keypair->privateKey);
            $this->line(implode("\n", array_slice($lines, 0, 3)));
            $this->line('...');
            $this->newLine();
            $this->warn('Private key was NOT saved. Use --save-to to persist it.');
        }

        $this->newLine();
        $this->info('Keypair generated successfully.');

        return self::SUCCESS;
    }
}
