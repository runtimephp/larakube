<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CreateOrganizationData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}

}
