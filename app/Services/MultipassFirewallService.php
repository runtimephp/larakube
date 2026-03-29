<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FirewallService;
use App\Data\CreateFirewallData;
use App\Data\FirewallData;
use App\Data\FirewallRuleData;
use Illuminate\Support\Collection;

final readonly class MultipassFirewallService implements FirewallService
{
    public function create(CreateFirewallData $data): FirewallData
    {
        return new FirewallData(
            externalId: 'multipass-'.$data->name,
            name: $data->name,
        );
    }

    public function addRule(string $id, FirewallRuleData $rule): FirewallData
    {
        $name = str_starts_with($id, 'multipass-')
            ? substr($id, strlen('multipass-'))
            : $id;

        return new FirewallData(
            externalId: $id,
            name: $name,
        );
    }

    public function list(): Collection
    {
        return collect();
    }

    public function find(string $id): ?FirewallData
    {
        return null;
    }

    public function delete(string $id): bool
    {
        return true;
    }
}
