<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PlatformRegion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlatformRegion
 */
final class PlatformRegionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'country' => $this->country,
            'city' => $this->city,
            'is_available' => $this->is_available,
        ];
    }
}
