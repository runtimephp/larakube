<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ManagementCluster;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ManagementCluster
 */
final class ManagementClusterResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'provider' => ProviderResource::make($this->provider)->resolve(),
            'region' => PlatformRegionResource::make($this->platformRegion)->resolve(),
            'status' => $this->status->value,
            'version' => KubernetesVersionResource::make($this->version)->resolve(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
