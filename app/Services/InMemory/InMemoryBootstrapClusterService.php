<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\BootstrapClusterService;

final class InMemoryBootstrapClusterService implements BootstrapClusterService
{
    /** @var list<string> */
    private array $clusters = [];

    private int $createCount = 0;

    public function addCluster(string $name): self
    {
        if (! in_array($name, $this->clusters, true)) {
            $this->clusters[] = $name;
        }

        return $this;
    }

    public function create(string $name): void
    {
        $this->createCount++;
        $this->clusters[] = $name;
    }

    public function destroy(string $name): void
    {
        $this->clusters = array_values(array_filter(
            $this->clusters,
            fn (string $cluster): bool => $cluster !== $name,
        ));
    }

    public function exists(string $name): bool
    {
        return in_array($name, $this->clusters, true);
    }

    public function createCount(): int
    {
        return $this->createCount;
    }
}
