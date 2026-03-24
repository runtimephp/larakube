<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Enums\CloudProviderType;

interface ServiceFactoryInterface
{
    public function makeServerService(CloudProviderType $type): ServerServiceContract;

    public function makeBaseService(CloudProviderType $type): object;
}
