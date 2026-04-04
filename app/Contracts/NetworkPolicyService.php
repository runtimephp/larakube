<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Http\Integrations\Kubernetes\Data\ManifestData;

interface NetworkPolicyService
{
    public function applyDefaultDeny(string $name, string $namespace): ManifestData;
}
