<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PrerequisiteChecker;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;

/** @codeCoverageIgnore */
final class ProcessPrerequisiteChecker implements PrerequisiteChecker
{
    public function hasBinary(string $name): bool
    {
        return $this->run("command -v {$name}")->successful();
    }

    public function isDockerRunning(): bool
    {
        return $this->hasBinary('docker') && $this->run('docker info')->successful();
    }

    private function run(string $command): \Illuminate\Contracts\Process\ProcessResult
    {
        return Process::quietly()->run($command);
    }
}
