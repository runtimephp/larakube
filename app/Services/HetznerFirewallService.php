<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FirewallService;
use App\Data\CreateFirewallData;
use App\Data\FirewallData;
use App\Data\FirewallRuleData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class HetznerFirewallService implements FirewallService
{
    public function __construct(private string $token) {}

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function create(CreateFirewallData $data): FirewallData
    {
        $response = Http::withToken($this->token)
            ->post('https://api.hetzner.cloud/v1/firewalls', [
                'name' => $data->name,
                'rules' => array_map($this->ruleToArray(...), $data->rules),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to create firewall on Hetzner.'));
        }

        return $this->mapFirewallData($response->json('firewall'));
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function addRule(string $id, FirewallRuleData $rule): FirewallData
    {
        $existing = $this->find($id);

        $allRules = $existing !== null ? $existing->rules : [];
        $allRules[] = $rule;

        $response = Http::withToken($this->token)
            ->post("https://api.hetzner.cloud/v1/firewalls/{$id}/actions/set_rules", [
                'rules' => array_map($this->ruleToArray(...), $allRules),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to add firewall rule on Hetzner.'));
        }

        return $this->find($id) ?? throw new RuntimeException('Firewall not found after adding rule.');
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function list(): Collection
    {
        $response = Http::withToken($this->token)
            ->get('https://api.hetzner.cloud/v1/firewalls');

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to list firewalls on Hetzner.'));
        }

        return collect($response->json('firewalls', []))
            ->map($this->mapFirewallData(...));
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function find(string $id): ?FirewallData
    {
        $response = Http::withToken($this->token)
            ->get("https://api.hetzner.cloud/v1/firewalls/{$id}");

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to find firewall on Hetzner.'));
        }

        return $this->mapFirewallData($response->json('firewall'));
    }

    /**
     * @throws ConnectionException
     */
    public function delete(string $id): bool
    {
        $response = Http::withToken($this->token)
            ->delete("https://api.hetzner.cloud/v1/firewalls/{$id}");

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $firewall
     */
    private function mapFirewallData(array $firewall): FirewallData
    {
        $rules = array_map(
            fn (array $rule): FirewallRuleData => FirewallRuleData::fromPortString(
                direction: $rule['direction'],
                protocol: $rule['protocol'],
                port: $rule['port'],
                sourceIps: $rule['source_ips'] ?? [],
                destinationIps: $rule['destination_ips'] ?? [],
            ),
            $firewall['rules'] ?? [],
        );

        return new FirewallData(
            externalId: $firewall['id'],
            name: $firewall['name'],
            rules: $rules,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function ruleToArray(FirewallRuleData $rule): array
    {
        $data = [
            'direction' => $rule->direction,
            'protocol' => $rule->protocol,
            'port' => $rule->toPortString(),
        ];

        if ($rule->direction === 'in') {
            $data['source_ips'] = $rule->sourceIps;
        } else {
            $data['destination_ips'] = $rule->destinationIps;
        }

        return $data;
    }
}
