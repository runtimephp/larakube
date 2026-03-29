<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\FirewallService;
use App\Data\CreateFirewallData;
use App\Data\FirewallData;
use App\Data\FirewallRuleData;
use Illuminate\Support\Collection;
use RuntimeException;

final class InMemoryFirewallService implements FirewallService
{
    /** @var Collection<int, FirewallData> */
    private Collection $firewalls;

    private bool $failCreate = false;

    private bool $failDelete = false;

    private int $nextId = 1;

    public function __construct()
    {
        $this->firewalls = collect();
    }

    public function addFirewall(FirewallData $firewall): self
    {
        $this->firewalls->push($firewall);

        $externalId = (string) $firewall->externalId;

        if (ctype_digit($externalId) && (int) $externalId >= $this->nextId) {
            $this->nextId = (int) $externalId + 1;
        }

        return $this;
    }

    public function shouldFailCreate(bool $fail = true): self
    {
        $this->failCreate = $fail;

        return $this;
    }

    public function shouldFailDelete(bool $fail = true): self
    {
        $this->failDelete = $fail;

        return $this;
    }

    public function create(CreateFirewallData $data): FirewallData
    {
        if ($this->failCreate) {
            throw new RuntimeException('Simulated API failure on create');
        }

        $firewall = new FirewallData(
            externalId: (string) $this->nextId++,
            name: $data->name,
            rules: $data->rules,
        );

        $this->firewalls->push($firewall);

        return $firewall;
    }

    public function addRule(string $id, FirewallRuleData $rule): FirewallData
    {
        $existing = $this->find($id);

        if ($existing === null) {
            throw new RuntimeException("Firewall {$id} not found.");
        }

        $updatedRules = [...$existing->rules, $rule];
        $updated = new FirewallData(
            externalId: $existing->externalId,
            name: $existing->name,
            rules: $updatedRules,
        );

        $this->firewalls = $this->firewalls->map(
            fn (FirewallData $fw): FirewallData => (string) $fw->externalId === $id ? $updated : $fw
        );

        return $updated;
    }

    public function list(): Collection
    {
        return $this->firewalls->values();
    }

    public function find(string $id): ?FirewallData
    {
        return $this->firewalls->first(
            fn (FirewallData $firewall): bool => (string) $firewall->externalId === $id
        );
    }

    public function delete(string $id): bool
    {
        if ($this->failDelete) {
            return false;
        }

        $before = $this->firewalls->count();
        $this->firewalls = $this->firewalls->reject(
            fn (FirewallData $firewall): bool => (string) $firewall->externalId === $id
        )->values();

        return $this->firewalls->count() < $before;
    }
}
