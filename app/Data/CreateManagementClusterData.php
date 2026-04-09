<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\KubernetesVersion;
use Illuminate\Contracts\Support\Arrayable;

final readonly class CreateManagementClusterData implements Arrayable
{
    public function __construct(
        public string $name,
        public string $providerId,
        public string $platformRegionId,
        public KubernetesVersion $version,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'provider_id' => $this->providerId,
            'region_id' => $this->platformRegionId,
            'version' => $this->version,
        ];
    }
}
