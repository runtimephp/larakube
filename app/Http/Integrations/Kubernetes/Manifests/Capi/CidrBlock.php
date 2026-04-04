<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi;

final readonly class CidrBlock
{
    public function __construct(
        public string $cidr,
    ) {}

    public function toString(): string
    {
        return $this->cidr;
    }
}
