<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\TenantQuotaData;
use App\Http\Integrations\Kubernetes\Data\ManifestData;

interface ResourceQuotaService
{
    public function apply(string $name, string $namespace, TenantQuotaData $tenantQuotaData): ManifestData;
}
