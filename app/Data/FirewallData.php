<?php

declare(strict_types=1);

namespace App\Data;

final readonly class FirewallData
{
    /**
     * @param  list<FirewallRuleData>  $rules
     */
    public function __construct(
        public int|string $externalId,
        public string $name,
        public array $rules = [],
    ) {}
}
