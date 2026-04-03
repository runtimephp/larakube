<?php

declare(strict_types=1);

namespace App\Contracts;

interface BootstrapClusterService
{
    public function create(string $name): void;

    public function destroy(string $name): void;

    public function exists(string $name): bool;
}
