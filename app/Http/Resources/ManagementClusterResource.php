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
            'provider' => $this->provider->slug->value,
            'provider_name' => $this->provider->name,
            'region' => $this->platformRegion->slug,
            'region_name' => $this->platformRegion->name,
            'status' => $this->status->value,
            'version' => $this->version->name,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
