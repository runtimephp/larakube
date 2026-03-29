<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateFirewallData;
use App\Data\FirewallData;
use App\Data\FirewallRuleData;
use Illuminate\Support\Collection;

interface FirewallService
{
    public function create(CreateFirewallData $data): FirewallData;

    public function addRule(string $id, FirewallRuleData $rule): FirewallData;

    public function list(): Collection;

    public function find(string $id): ?FirewallData;

    public function delete(string $id): bool;
}
