<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\ManagementCluster;

final readonly class ManagementClusterData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $providerId,
        public string $platformRegionId,
        public string $status,
        public string $version,
    ) {}

    public static function fromModel(ManagementCluster $cluster): self
    {
        return new self(
            id: $cluster->id,
            name: $cluster->name,
            providerId: $cluster->provider_id,
            platformRegionId: $cluster->platform_region_id,
            status: $cluster->status->value,
            version: $cluster->version->name,
        );
    }

    /**
     * @param  array{id: string, name: string, provider_id: string, platform_region_id: string, status: string, version: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            providerId: $data['provider_id'],
            platformRegionId: $data['platform_region_id'],
            status: $data['status'],
            version: $data['version'],
        );
    }

    /**
     * @return array{id: string, name: string, provider_id: string, platform_region_id: string, status: string, version: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'provider_id' => $this->providerId,
            'platform_region_id' => $this->platformRegionId,
            'status' => $this->status,
            'version' => $this->version,
        ];
    }
}
