<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\KubernetesVersionCast;
use App\Data\KubernetesVersionData;
use App\Enums\ManagementClusterStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ManagementClusterFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $provider_id
 * @property-read string $platform_region_id
 * @property-read string $name
 * @property-read string|null $kubeconfig
 * @property-read ManagementClusterStatus $status
 * @property-read KubernetesVersionData $version
 * @property-read Provider $provider
 * @property-read PlatformRegion $platformRegion
 */
final class ManagementCluster extends Model
{
    /** @use HasFactory<ManagementClusterFactory> */
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
            'platform_region_id' => 'string',
            'provider_id' => 'string',
            'name' => 'string',
            'kubeconfig' => 'encrypted',
            'version' => KubernetesVersionCast::class,
            'status' => ManagementClusterStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function platformRegion(): BelongsTo
    {
        return $this->belongsTo(PlatformRegion::class);
    }
}
