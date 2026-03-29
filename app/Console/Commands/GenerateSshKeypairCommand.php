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

            file_put_contents($expandedPath, $keypair->privateKey);
            chmod($expandedPath, 0600);

            file_put_contents($expandedPath.'.pub', $keypair->publicKey);

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
