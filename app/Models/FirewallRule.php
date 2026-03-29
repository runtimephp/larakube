<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\FirewallRuleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $firewall_id
 * @property-read string $direction
 * @property-read string $protocol
 * @property-read int $port_start
 * @property-read int $port_end
 * @property-read list<string>|null $source_ips
 * @property-read Firewall $firewall
 */
final class FirewallRule extends Model
{
    /** @use HasFactory<FirewallRuleFactory> */
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
            'firewall_id' => 'string',
            'direction' => 'string',
            'protocol' => 'string',
            'port_start' => 'integer',
            'port_end' => 'integer',
            'source_ips' => 'array',
        ];
    }

    /** @return BelongsTo<Firewall, $this> */
    public function firewall(): BelongsTo
    {
        return $this->belongsTo(Firewall::class);
    }
}
