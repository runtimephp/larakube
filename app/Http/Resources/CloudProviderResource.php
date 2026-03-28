<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CloudProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CloudProvider
 */
final class CloudProviderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'is_verified' => $this->is_verified,
        ];
    }
}
