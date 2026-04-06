<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlatformRegionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $provider_id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string|null $country
 * @property-read string|null $city
 * @property-read bool $is_available
 * @property-read array<string, mixed>|null $metadata
 */
final class PlatformRegion extends Model
{
    /** @use HasFactory<PlatformRegionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'provider_id' => 'string',
            'name' => 'string',
            'slug' => 'string',
            'country' => 'string',
            'city' => 'string',
            'is_available' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
