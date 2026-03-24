<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\CloudProviderType;
use SensitiveParameter;

final readonly class CreateCloudProviderData
{
    public function __construct(
        public string $name,
        public CloudProviderType $type,
        #[SensitiveParameter]
        public string $apiToken,
    ) {}
}
