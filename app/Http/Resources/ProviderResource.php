<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Provider
 */
final class ProviderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug->value,
            'is_active' => $this->is_active,
            'has_api_token' => $this->api_token !== null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
