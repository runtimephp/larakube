<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CreateFirewallData
{
    /**
     * @param  list<FirewallRuleData>  $rules
     */
    public function __construct(
        public string $name,
        public string $infrastructure_id,
        public array $rules = [],
    ) {}
}
