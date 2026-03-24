<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServerStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ServerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $organization_id
 * @property-read string $cloud_provider_id
 * @property-read string $external_id
 * @property-read string $name
 * @property-read ServerStatus $status
 * @property-read string $type
 * @property-read string $region
 * @property-read string|null $ipv4
 * @property-read string|null $ipv6
 * @property-read array|null $metadata
 * @property-read Organization $organization
 * @property-read CloudProvider $cloudProvider
 */
final class Server extends Model
{
    /** @use HasFactory<ServerFactory> */
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
            'organization_id' => 'string',
            'cloud_provider_id' => 'string',
            'external_id' => 'string',
            'name' => 'string',
            'status' => ServerStatus::class,
            'type' => 'string',
            'region' => 'string',
            'ipv4' => 'string',
            'ipv6' => 'string',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<CloudProvider, $this> */
    public function cloudProvider(): BelongsTo
    {
        return $this->belongsTo(CloudProvider::class);
    }
}
