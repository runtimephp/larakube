<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Enums\CloudProviderType;

interface CloudProviderClientFactoryInterface
{
    public function make(CloudProviderType $type): CloudProviderClient;
}
