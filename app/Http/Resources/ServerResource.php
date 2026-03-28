<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Server
 */
final class ServerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'type' => $this->type,
            'region' => $this->region,
            'ipv4' => $this->ipv4,
            'ipv6' => $this->ipv6,
            'external_id' => $this->external_id,
            'cloud_provider_id' => $this->cloud_provider_id,
            'infrastructure_id' => $this->infrastructure_id,
        ];
    }
}
