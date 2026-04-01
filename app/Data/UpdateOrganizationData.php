<?php

declare(strict_types=1);

namespace App\Data;

final readonly class UpdateOrganizationData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}
