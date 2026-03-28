<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Infrastructure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Infrastructure
 */
final class InfrastructureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status->value,
            'cloud_provider_id' => $this->cloud_provider_id,
        ];
    }
}
