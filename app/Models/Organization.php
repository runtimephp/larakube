<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $name
 * @property-read string $slug
 * @property-read string $description
 * @property-read string $logo
 * @property-read Collection<int, CloudProvider> $cloudProviders
 */
final class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array|string[]
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'slug' => 'string',
            'name' => 'string',
            'description' => 'string',
            'logo' => 'string',
        ];
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(OrganizationUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /** @return HasMany<CloudProvider, $this> */
    public function cloudProviders(): HasMany
    {
        return $this->hasMany(CloudProvider::class);
    }
}
