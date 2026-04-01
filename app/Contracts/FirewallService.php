<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateFirewallData;
use App\Data\FirewallData;
use App\Data\FirewallRuleData;
use Illuminate\Support\Collection;

/**
 * @see ADR-0005, ADR-0009 — Write methods to be removed; refactoring to CloudManager driver pattern
 */
interface FirewallService
{
    public function create(CreateFirewallData $data): FirewallData;

    public function addRule(string $id, FirewallRuleData $rule): FirewallData;

    public function list(): Collection;

    public function find(string $id): ?FirewallData;

    public function delete(string $id): bool;
}
