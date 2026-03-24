<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InfrastructureStatus;
use Carbon\CarbonImmutable;
use Database\Factories\FirewallFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $infrastructure_id
 * @property-read string $name
 * @property-read string|null $external_firewall_id
 * @property-read InfrastructureStatus $status
 * @property-read Infrastructure $infrastructure
 */
final class Firewall extends Model
{
    /** @use HasFactory<FirewallFactory> */
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
            'infrastructure_id' => 'string',
            'name' => 'string',
            'external_firewall_id' => 'string',
            'status' => InfrastructureStatus::class,
        ];
    }

    /** @return BelongsTo<Infrastructure, $this> */
    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
