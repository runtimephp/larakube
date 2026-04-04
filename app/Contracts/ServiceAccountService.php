<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Http\Integrations\Kubernetes\Data\ServiceAccountData;

interface ServiceAccountService
{
    public function create(string $name, string $namespace): ServiceAccountData;
}
