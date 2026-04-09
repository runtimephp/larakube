<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Data\KubernetesVersionData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin KubernetesVersionData
 */
final class KubernetesVersionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'is_supported' => $this->isSupported(),
            'end_of_life' => $this->endOfLife->toIso8601String(),
        ];
    }
}
