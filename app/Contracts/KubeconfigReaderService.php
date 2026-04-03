<?php

declare(strict_types=1);

namespace App\Contracts;

interface KubeconfigReaderService
{
    public function read(string $clusterName): string;
}
