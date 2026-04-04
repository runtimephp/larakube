<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BootstrapClusterService;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/** @codeCoverageIgnore */
final class KindBootstrapClusterService implements BootstrapClusterService
{
    public function create(string $name): void
    {
        $result = Process::run("kind create cluster --name {$name}");

        if ($result->failed()) {
            throw new RuntimeException("Failed to create kind cluster '{$name}': {$result->errorOutput()}");
        }
    }

    public function destroy(string $name): void
    {
        $result = Process::run("kind delete cluster --name {$name}");

        if ($result->failed()) {
            throw new RuntimeException("Failed to delete kind cluster '{$name}': {$result->errorOutput()}");
        }
    }

    public function exists(string $name): bool
    {
        $result = Process::run('kind get clusters');

        if ($result->failed()) {
            return false;
        }

        $clusters = array_filter(explode("\n", $result->output()));

        return in_array($name, $clusters, true);
    }
}
