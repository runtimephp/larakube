<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProviderSlug;
use Carbon\CarbonImmutable;
use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $name
 * @property-read ProviderSlug $slug
 * @property-read string|null $api_token
 * @property-read bool $is_active
 */
final class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
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
            'name' => 'string',
            'slug' => ProviderSlug::class,
            'api_token' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }
}
