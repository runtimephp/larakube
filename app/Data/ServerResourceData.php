<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ServerResourceData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $status,
        public string $type,
        public string $region,
        public ?string $ipv4,
        public ?string $ipv6,
        public string $externalId,
        public string $cloudProviderId,
        public ?string $infrastructureId,
    ) {}

    /**
     * @param  array{id: string, name: string, status: string, type: string, region: string, ipv4: string|null, ipv6: string|null, external_id: string, cloud_provider_id: string, infrastructure_id: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            status: $data['status'],
            type: $data['type'],
            region: $data['region'],
            ipv4: $data['ipv4'] ?? null,
            ipv6: $data['ipv6'] ?? null,
            externalId: $data['external_id'],
            cloudProviderId: $data['cloud_provider_id'],
            infrastructureId: $data['infrastructure_id'] ?? null,
        );
    }

    /**
     * @return array{id: string, name: string, status: string, type: string, region: string, ipv4: string|null, ipv6: string|null, external_id: string, cloud_provider_id: string, infrastructure_id: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'type' => $this->type,
            'region' => $this->region,
            'ipv4' => $this->ipv4,
            'ipv6' => $this->ipv6,
            'external_id' => $this->externalId,
            'cloud_provider_id' => $this->cloudProviderId,
            'infrastructure_id' => $this->infrastructureId,
        ];
    }
}
