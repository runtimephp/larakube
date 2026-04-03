<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\PrerequisiteChecker;

final class InMemoryPrerequisiteChecker implements PrerequisiteChecker
{
    /** @var list<string> */
    private array $available = [];

    private bool $dockerRunning = true;

    /**
     * @param  list<string>  $binaries
     */
    public function setAvailable(array $binaries): self
    {
        $this->available = $binaries;

        return $this;
    }

    public function setDockerRunning(bool $running): self
    {
        $this->dockerRunning = $running;

        return $this;
    }

    public function hasBinary(string $name): bool
    {
        return in_array($name, $this->available, true);
    }

    /**
     * Returns true only if the docker binary is available AND the daemon is running.
     */
    public function isDockerRunning(): bool
    {
        return $this->dockerRunning && $this->hasBinary('docker');
    }
}
