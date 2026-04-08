<?php

declare(strict_types=1);

namespace App\Data;

final readonly class RegionData
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $country,
        public string $city,
        public array $metadata,
    ) {}
}
