<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CreateNetworkData
{
    public function __construct(
        public string $name,
        public string $cidr,
        public string $infrastructure_id,
    ) {}
}
