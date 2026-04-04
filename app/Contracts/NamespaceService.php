<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Http\Integrations\Kubernetes\Data\NamespaceData;

interface NamespaceService
{
    public function create(string $name): NamespaceData;
}
