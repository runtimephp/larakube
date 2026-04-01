<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasSlug;
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
 * @property-read Collection<int, Server> $servers
 * @property-read Collection<int, Infrastructure> $infrastructures
 */
final class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    use HasSlug;
    use HasUuids;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

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

    /** @return BelongsToMany<User, $this, OrganizationUser, 'pivot'> */
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

    /** @return HasMany<Server, $this> */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /** @return HasMany<Infrastructure, $this> */
    public function infrastructures(): HasMany
    {
        return $this->hasMany(Infrastructure::class);
    }
}
