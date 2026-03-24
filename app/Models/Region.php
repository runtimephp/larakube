<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $cloud_provider_id
 * @property-read string $internal_name
 * @property-read string $provider_region
 * @property-read string|null $description
 * @property-read CloudProvider $cloudProvider
 * @property-read Collection<int, Infrastructure> $infrastructures
 */
final class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
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
            'cloud_provider_id' => 'string',
            'internal_name' => 'string',
            'provider_region' => 'string',
            'description' => 'string',
        ];
    }

    /** @return BelongsTo<CloudProvider, $this> */
    public function cloudProvider(): BelongsTo
    {
        return $this->belongsTo(CloudProvider::class);
    }

    /** @return HasMany<Infrastructure, $this> */
    public function infrastructures(): HasMany
    {
        return $this->hasMany(Infrastructure::class);
    }
}
