<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Services\BastionSshExecutor;

final readonly class HealthCheck implements StepHandler
{
    public function __construct(private BastionSshExecutor $ssh) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $output = $this->ssh->execute(
            $infrastructure,
            'kubectl get nodes --no-headers',
        );

        $lines = array_filter(explode("\n", mb_trim($output)));

        foreach ($lines as $line) {
            if (! str_contains($line, 'Ready')) {
                throw new RetryStepException("Not all nodes are Ready: {$line}");
            }

            if (str_contains($line, 'NotReady')) {
                throw new RetryStepException("Node is NotReady: {$line}");
            }
        }

        if ($lines === []) {
            throw new RetryStepException('No nodes returned from kubectl get nodes.');
        }
    }
}
