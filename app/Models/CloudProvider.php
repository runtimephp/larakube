<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CloudProviderType;
use Carbon\CarbonImmutable;
use Database\Factories\CloudProviderFactory;
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
 * @property-read string $organization_id
 * @property-read string $name
 * @property-read CloudProviderType $type
 * @property-read string $api_token
 * @property-read bool $is_verified
 * @property-read Collection<int, Server> $servers
 * @property-read Organization $organization
 */
final class CloudProvider extends Model
{
    /** @use HasFactory<CloudProviderFactory> */
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
            'name' => 'string',
            'type' => CloudProviderType::class,
            'api_token' => 'encrypted',
            'is_verified' => 'boolean',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<Server, $this> */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
