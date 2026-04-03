<?php

declare(strict_types=1);

namespace App\Contracts;

interface PrerequisiteChecker
{
    public function hasBinary(string $name): bool;

    public function isDockerRunning(): bool;
}
