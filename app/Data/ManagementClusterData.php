<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\ManagementCluster;

final readonly class ManagementClusterData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $provider,
        public string $region,
        public string $status,
    ) {}

    public static function fromModel(ManagementCluster $cluster): self
    {
        return new self(
            id: $cluster->id,
            name: $cluster->name,
            provider: $cluster->provider,
            region: $cluster->region,
            status: $cluster->status->value,
        );
    }

    /**
     * @param  array{id: string, name: string, provider: string, region: string, status: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            provider: $data['provider'],
            region: $data['region'],
            status: $data['status'],
        );
    }

    /**
     * @return array{id: string, name: string, provider: string, region: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'provider' => $this->provider,
            'region' => $this->region,
            'status' => $this->status,
        ];
    }
}
