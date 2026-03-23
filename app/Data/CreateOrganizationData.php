<?php

declare(strict_types=1);

namespace App\Data;

final class CreateOrganizationData
{

    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
    ) {}

}
