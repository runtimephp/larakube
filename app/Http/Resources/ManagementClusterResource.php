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
            'provider' => $this->provider,
            'region' => $this->region,
            'status' => $this->status->value,
        ];
    }
}
