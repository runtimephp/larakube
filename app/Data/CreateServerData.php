<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CreateServerData
{
    public function __construct(
        public string $name,
        public string $type,
        public string $image,
        public string $region,
        public string $infrastructure_id,
    ) {}
}
